<?php
class EseraGaszaehler extends IPSModule 
{
    public function Create()
	{
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("CounterID", 0);
        $this->RegisterPropertyInteger("Impulses", 1000);
        $this->RegisterPropertyFloat("AnnualLimit", 0.9692);
        $this->RegisterPropertyInteger("LimitActive", 100);
		
		$this->RegisterVariableInteger("DailyResetTime", "Tages Reset Time", "~UnixTimestamp", 1);
	    $this->RegisterVariableInteger("MonthlyResetTime", "Monats Reset Time", "~UnixTimestamp", 2);
		$this->RegisterVariableInteger("YearlyResetTime", "Jahres Reset Time", "~UnixTimestamp", 3);
		
		$this->RegisterVariableInteger("Counter", "Counter", "", 10);
		$this->RegisterVariableFloat("Verbrauch", "Verbrauch", "~Gas", 11);
		
		$this->RegisterVariableInteger("TagCounter", "Counter Tag", "", 20);
		$this->RegisterVariableFloat("VerbrauchTagm", "Verbrauch am Tag in m³", "~Gas", 21);
		$this->RegisterVariableFloat("VerbrauchTagkwh", "Verbrauch am Tag in kwh", "Kirsch.kWh", 22);
		$this->RegisterVariableFloat("VerbrauchVortagm", "Verbrauch Vortag in m³", "~Gas", 23);		
		$this->RegisterVariableFloat("VerbrauchVortagkwh", "Verbrauch Vortag in kWh", "Kirsch.kWh", 24);
		$this->RegisterVariableFloat("VerbrauchVortagEuro", "Verbrauch Vortag in €", "~Euro", 25);

	    $ArchiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagm"), true);
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagkwh"), true);
		AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagEuro"), true);

		
		$this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounterG($_IPS[\'TARGET\']);');
		$this->RegisterTimer("DailyReset", 0, 'ESERA_ResetPowerMeterDaily($_IPS[\'TARGET\']);');
		
	}
	
    public function Destroy()
	{
        //Never delete this line!
        parent::Destroy();
    }
	
    public function ApplyChanges()
	{
        //Never delete this line!
        parent::ApplyChanges();

        $this->SetTimerInterval("Refresh", 180 * 1000);
        $this->SetDailyTimerInterval();
        //$this->SetMonthlyTimerInterval();
        //$this->SetYearlyTimerInterval();    
    }

	public function ReceiveData($JSONString) 
	{
        // not implemented   
    }
	
	public function ResetPowerMeterDaily()
	{
        $this->SetDailyTimerInterval();
		//$this->SetMonthlyTimerInterval();
		//$this->SetYearlyTimerInterval();
        SetValue($this->GetIDForIdent("TagCounter"), 0);
        SetValue($this->GetIDForIdent("VerbrauchVortagm"), GetValue($this->GetIDForIdent("VerbrauchTagm")));
		SetValue($this->GetIDForIdent("VerbrauchVortagkwh"), GetValue($this->GetIDForIdent("VerbrauchTagkwh")));
		SetValue($this->GetIDForIdent("VerbrauchVortagEuro"), GetValue($this->GetIDForIdent("VerbrauchTagkwh") * 0.1066));
        SetValue($this->GetIDForIdent("VerbrauchTagm"), 0);
		SetValue($this->GetIDForIdent("VerbrauchTagkwh"), 0);
    }
	
	public function RefreshCounterG()
	{
       $this->calculate();   
    }
	
	private function Calculate()
	{
		// Jahresgrenzwert
        $AnnualLimit = $this->ReadPropertyFloat("AnnualLimit");
		$CounterOld = GetValue($this->GetIDForIdent("Counter"));
		$CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
		$delta = $CounterNew - $CounterOld;
		$Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
		$delta_qm = ($delta * $Factor) * 20;
		
		SetValue($this->GetIDForIdent("Counter"), $CounterNew);
		SetValue($this->GetIDForIdent("Verbrauch"), $delta_qm);
		
		//Counter Tag
		$CounterTag = GetValue($this->GetIDForIdent("TagCounter")) + $delta;
        SetValue($this->GetIDForIdent("TagCounter"), $CounterTag);
        SetValue($this->GetIDForIdent("VerbrauchTagm"), $CounterTag * $Factor);
		$Zustandszahl = 0.9692;
		$Brennwert = 11.293;
		//$FactorKWh = 0.9692*11.293;
		SetValue($this->GetIDForIdent("VerbrauchTagkwh"), $CounterTag * $Factor * $Zustandszahl * $Brennwert);
		echo "Zustandszahl = $AnnualLimit \r\n";
		$this->DebugMessage("Counter", "Zustandszahl: " . $AnnualLimit);
        //$this->DebugMessage("Counter", "CounterNew: " . $CounterNew);
	}
	
	private function DebugMessage($Sender, $Message)
	{
        $this->SendDebug($Sender, $Message, 0);
    }
	
	private function GetFactor($Impulses)
	{
        switch ($Impulses){
            case 250:
              return (0.004);
            break;
              
            case 500:
              return (0.002);
            break;
              
            case 800:
              return (0.00125);
            break;
              
            case 1000:
              return (0.001);
            break;
              
            case 2000:
              return (0.0005);
            break;
        }    
    }
		
	protected function SetDailyTimerInterval()
	{
    	$Now = new DateTime(); 
		$Target = new DateTime(); 
		$Target->modify('+1 day'); 
		$Target->setTime(0,0,1); 
		$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
		$Interval = $Diff * 1000;  
	   	$this->SetTimerInterval("DailyReset", $Interval);
		echo "Interval = $Interval \r\n";
		echo "Target =  $Target->getTimestamp()";
		//SetValue($this->GetIDForIdent("DailyResetTime"), $Target);
    }
}
?>
