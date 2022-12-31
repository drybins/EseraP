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
        $this->RegisterPropertyFloat("Zustandszahl", 0.9692);
        $this->RegisterPropertyFloat("Brennwert", 11.293);
		
		$this->RegisterPropertyFloat("Centkwh", 0.1066);
	    
		$this->RegisterVariableInteger("HourResetTime", "Stunden Reset Time", "~UnixTimestamp", 0);
		$this->RegisterVariableInteger("DailyResetTime", "Tages Reset Time", "~UnixTimestamp", 1);
	    $this->RegisterVariableInteger("MonthlyResetTime", "Monats Reset Time", "~UnixTimestamp", 2);
		$this->RegisterVariableInteger("YearlyResetTime", "Jahres Reset Time", "~UnixTimestamp", 3);
		
		$this->RegisterVariableInteger("Counter", "Counter", "", 10);
		$this->RegisterVariableFloat("Verbrauch", "Verbrauch", "~Gas", 11);
	    $this->RegisterVariableFloat("Zähler", "Zähler", "~Gas", 12);
	    
	    $this->RegisterVariableFloat("VerbrauchTagEuro", "Verbrauch am Tag in Euro", "~Euro", 13);
		
		$this->RegisterVariableInteger("StdCounter", "Counter Stunde", "", 15);
		$this->RegisterVariableFloat("VerbrauchStdm", "Verbrauch in der Stunde in m³", "~Gas", 16);
		$this->RegisterVariableFloat("VerbrauchStdkwh", "Verbrauch in der Stunde in kwh", "Kirsch.kWh", 17); 
		
		$this->RegisterVariableInteger("TagCounter", "Counter Tag", "", 20);
		$this->RegisterVariableFloat("VerbrauchTagm", "Verbrauch am Tag in m³", "~Gas", 21);
		$this->RegisterVariableFloat("VerbrauchTagkwh", "Verbrauch am Tag in kwh", "Kirsch.kWh", 22);
		$this->RegisterVariableFloat("VerbrauchVortagm", "Verbrauch Vortag in m³", "~Gas", 23);		
		$this->RegisterVariableFloat("VerbrauchVortagkwh", "Verbrauch Vortag in kWh", "Kirsch.kWh", 24);
		$this->RegisterVariableFloat("VerbrauchVortagEuro", "Verbrauch Vortag in €", "~Euro", 25);
	    
		$this->RegisterVariableInteger("MonatCounter", "Counter Monat", "", 30);
        $this->RegisterVariableFloat("VerbrauchMonatm", "Verbrauch im Monat in m³", "~Gas", 31);
        $this->RegisterVariableFloat("VerbrauchMonatkwh", "Verbrauch im Monat in kwh", "Kirsch.kWh", 32);
		$this->RegisterVariableFloat("VerbrauchVormonatm", "Verbrauch Vormonat in m³", "~Gas", 33);		
		$this->RegisterVariableFloat("VerbrauchVormonatkwh", "Verbrauch Vormonat in kWh", "Kirsch.kWh", 34);
		$this->RegisterVariableFloat("VerbrauchVormonatEuro", "Verbrauch Vormonat in €", "~Euro", 35);
		
		$this->RegisterVariableInteger("JahrCounter", "Counter Jahr", "", 40);
        $this->RegisterVariableFloat("VerbrauchJahrm", "Verbrauch im Jahr in m³", "~Gas", 41);
        $this->RegisterVariableFloat("VerbrauchJahrkwh", "Verbrauch im Jahr in kwh", "Kirsch.kWh", 42);
		$this->RegisterVariableFloat("VerbrauchVorjahrm", "Verbrauch Vorjahr in m³", "~Gas", 43);		
		$this->RegisterVariableFloat("VerbrauchVorjahrkwh", "Verbrauch Vorjahr in kWh", "Kirsch.kWh", 44);
		$this->RegisterVariableFloat("VerbrauchVorjahrEuro", "Verbrauch Vorjahr in €", "~Euro", 45);

	    $ArchiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagm"), true);
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagkwh"), true);
		AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVortagEuro"), true);
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVormonatm"), true);
	    AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVormonatkwh"), true);
		AC_SetLoggingStatus($ArchiveHandlerID[0], $this->GetIDForIdent("VerbrauchVormonatEuro"), true);
		
		$this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounterG($_IPS[\'TARGET\']);');
		$this->RegisterTimer("HourReset", 0, 'ESERA_ResetPowerMeterHour($_IPS[\'TARGET\']);');
		$this->RegisterTimer("DailyReset", 0, 'ESERA_ResetPowerMeterDaily($_IPS[\'TARGET\']);');
		$this->RegisterTimer("MonthlyReset", 0, 'ESERA_ResetPowerMeterMonthly($_IPS[\'TARGET\']);');
        $this->RegisterTimer("YearlyReset", 0, 'ESERA_ResetPowerMeterYearly($_IPS[\'TARGET\']);');
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

        $this->SetTimerInterval("Refresh", 60 * 1000);
		$this->SetHourTimerInterval();
        $this->SetDailyTimerInterval();
        $this->SetMonthlyTimerInterval();
        $this->SetYearlyTimerInterval();    
    }

	public function ReceiveData($JSONString) 
	{
        // not implemented   
    }
	
		public function ResetPowerMeterHour()
	{
		$this->SetHourTimerInterval();
		$Centkwh = $this->ReadPropertyFloat("Centkwh");
		SetValue($this->GetIDForIdent("StdCounter"), 0);
        SetValue($this->GetIDForIdent("VerbrauchStdm"), 0);
		SetValue($this->GetIDForIdent("VerbrauchStdkwh"), 0);
	}
	
	public function ResetPowerMeterDaily()
	{
        $this->SetDailyTimerInterval();
		$this->SetMonthlyTimerInterval();
		$this->SetYearlyTimerInterval();
        $Centkwh = $this->ReadPropertyFloat("Centkwh");
		SetValue($this->GetIDForIdent("TagCounter"), 0);
        SetValue($this->GetIDForIdent("VerbrauchVortagm"), GetValue($this->GetIDForIdent("VerbrauchTagm")));
		SetValue($this->GetIDForIdent("VerbrauchVortagkwh"), GetValue($this->GetIDForIdent("VerbrauchTagkwh")));
		$ID1 = $this->GetIDForIdent("VerbrauchVortagkwh");
		SetValue($this->GetIDForIdent("VerbrauchVortagEuro"), GetValue($ID1) * 0.1066);
        SetValue($this->GetIDForIdent("VerbrauchTagm"), 0);
		SetValue($this->GetIDForIdent("VerbrauchTagkwh"), 0);
		$this->DebugMessage("GasZähler_ResetPowerMeterDaily", "Cent je KwH: " . $Centkwh);
    }
	
	public function ResetPowerMeterMonthly()
	{
		$this->DebugMessage("GasZähler", "ResetPowerMeterMonthly started");
		$Centkwh = $this->ReadPropertyFloat("Centkwh");
        SetValue($this->GetIDForIdent("MonatCounter"), 0);
        SetValue($this->GetIDForIdent("VerbrauchVormonatm"), GetValue($this->GetIDForIdent("VerbrauchMonatm")));
		SetValue($this->GetIDForIdent("VerbrauchVormonatkwh"), GetValue($this->GetIDForIdent("VerbrauchMonatkwh")));
		$ID2 = $this->GetIDForIdent("VerbrauchVormonatkwh");
		SetValue($this->GetIDForIdent("VerbrauchVormonatEuro"), GetValue($ID2) * 0.1066);
		//SetValue($this->GetIDForIdent("VerbrauchVormonatEuro"), GetValue($this->GetIDForIdent("VerbrauchVormonatkwh") * 0.1066));
        SetValue($this->GetIDForIdent("VerbrauchMonatm"), 0);
		SetValue($this->GetIDForIdent("VerbrauchMonatkwh"), 0);
    }
	
    public function ResetPowerMeterYearly()
	{
        SetValue($this->GetIDForIdent("JahrCounter"), 0);
        SetValue($this->GetIDForIdent("VerbrauchVorjahrm"), GetValue($this->GetIDForIdent("VerbrauchJahrm")));
		SetValue($this->GetIDForIdent("VerbrauchVorjahrkwh"), GetValue($this->GetIDForIdent("VerbrauchJahrkwh")));
		$ID3 = $this->GetIDForIdent("VerbrauchVorjahrkwh");
		SetValue($this->GetIDForIdent("VerbrauchVorjahrEuro"), GetValue($ID3) * 0.1066);
		//SetValue($this->GetIDForIdent("VerbrauchVorjahrEuro"), GetValue($this->GetIDForIdent("VerbrauchVorjahrkwh") * 0.1066));
        SetValue($this->GetIDForIdent("VerbrauchJahrm"), 0);
		SetValue($this->GetIDForIdent("VerbrauchJahrkwh"), 0);
    }
	
	public function RefreshCounterG()
	{
       $this->calculate();   
    }
	
	private function Calculate()
	{
		// Jahresgrenzwert
        $Zustandszahl = $this->ReadPropertyFloat("Zustandszahl");
		$Brennwert = $this->ReadPropertyFloat("Brennwert");
		$Centkwh = $this->ReadPropertyFloat("Centkwh");
		$CounterOld = GetValue($this->GetIDForIdent("Counter"));
		//if($CounterOld == 0)
		//{
		//	SetValue($this->GetIDForIdent("Counter"), $CounterOld);
		//}
		//Else
		//{
			$CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
			$delta = $CounterNew - $CounterOld;
			$Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
			$delta_qm = ($delta * $Factor) * 20;
		
			SetValue($this->GetIDForIdent("Counter"), $CounterNew);
			SetValue($this->GetIDForIdent("Verbrauch"), $delta_qm);
		//}
		// Only for debugging
        $this->DebugMessage("GasZähler", "CounterOld: " . $CounterOld);
        $this->DebugMessage("GasZähler", "CounterNew: " . $CounterNew);
        $this->DebugMessage("GasZähler", "Delta: " . $delta);
		 $this->DebugMessage("GasZähler", "CounterID: " . GetValue($this->ReadPropertyInteger("CounterID")));
        $this->DebugMessage("GasZähler", "Factor: " . $Factor);
        $this->DebugMessage("GasZähler", "Delta kWh: " . $delta_qm);
		$this->DebugMessage("GasZähler", "Zustandszahl: " . $Zustandszahl);
		$this->DebugMessage("GasZähler", "Brennwert: " . $Brennwert);
		
		//Counter Std
		$CounterStd = GetValue($this->GetIDForIdent("StdCounter")) + $delta;
        SetValue($this->GetIDForIdent("StdCounter"), $CounterStd);
        SetValue($this->GetIDForIdent("VerbrauchStdm"), $CounterStd * $Factor);
		SetValue($this->GetIDForIdent("VerbrauchStdkwh"), $CounterStd * $Factor * $Zustandszahl * $Brennwert);
		
		//Counter Tag
		$CounterTag = GetValue($this->GetIDForIdent("TagCounter")) + $delta;
        SetValue($this->GetIDForIdent("TagCounter"), $CounterTag);
        SetValue($this->GetIDForIdent("VerbrauchTagm"), $CounterTag * $Factor);
		//$Zustandszahl = 0.9692;
		//$Brennwert = 11.293;
		//$FactorKWh = 0.9692*11.293;
		SetValue($this->GetIDForIdent("VerbrauchTagkwh"), $CounterTag * $Factor * $Zustandszahl * $Brennwert);
		$ID1 = $this->GetIDForIdent("VerbrauchTagkwh");
		SetValue($this->GetIDForIdent("VerbrauchTagEuro"), GetValue($ID1) * 0.1066);
		//echo "Zustandszahl = $AnnualLimit \r\n";

		// Counter Monat  
        $CounterMonat = GetValue($this->GetIDForIdent("MonatCounter")) + $delta;
        SetValue($this->GetIDForIdent("MonatCounter"), $CounterMonat);
        SetValue($this->GetIDForIdent("VerbrauchMonatm"), $CounterMonat * $Factor);
		SetValue($this->GetIDForIdent("VerbrauchMonatkwh"), $CounterMonat * $Factor * $Zustandszahl * $Brennwert);
		
		// Counter Jahr  
        $CounterJahr = GetValue($this->GetIDForIdent("JahrCounter")) + $delta;
        SetValue($this->GetIDForIdent("JahrCounter"), $CounterJahr);
        SetValue($this->GetIDForIdent("VerbrauchJahrm"), $CounterJahr * $Factor);
		SetValue($this->GetIDForIdent("VerbrauchJahrkwh"), $CounterJahr * $Factor * $Zustandszahl * $Brennwert);

	}
	
	private function DebugMessage($Sender, $Message)
	{
        $this->SendDebug($Sender, $Message, 0);
    }
	
	private function GetFactor($Impulses)
	{
        switch ($Impulses){
            case 250:
              return (0.04);
            break;
              
            case 500:
              return (0.00);
            break;
              
            case 800:
              return (0.0125);
            break;
              
            case 1000:
              return (0.01);
            break;
              
            case 2000:
              return (0.005);
            break;
        }    
    }
	
		protected function SetHourTimerInterval()
	{
    	$Now = new DateTime(); 
		$Target = new DateTime(); 
		//$Target->modify('+1 hour'); 
		$stunde =  Date('H');
		$stunde++; 
		$Target->setTime($stunde,0,1); 
		$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
		$Tar = $Target->getTimestamp();
		$Interval = $Diff * 1000;  
	   	$this->SetTimerInterval("HourReset", $Interval);
		SetValue($this->GetIDForIdent("HourResetTime"), $Tar);
		$this->DebugMessage("GasZähler", "Tar: " . $Interval);
	}
		
	protected function SetDailyTimerInterval()
	{
    	$Now = new DateTime(); 
		$Target = new DateTime(); 
		$Target->modify('+1 day'); 
		$Target->setTime(0,0,1); 
		$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
		$Tar = $Target->getTimestamp();
		$Interval = $Diff * 1000;  
	   	$this->SetTimerInterval("DailyReset", $Interval);
		SetValue($this->GetIDForIdent("DailyResetTime"), $Tar);
    }
	protected function SetMonthlyTimerInterval()
	{
        $Now = new DateTime(); 
		$Target = new DateTime(); 
		$Target->modify('first day of next month');
		$Target->setTime(0,0,5); 
		$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
		$Interval = $Diff * 1000;  
		$this->SetTimerInterval("MonthlyReset", $Interval);
		SetValue($this->GetIDForIdent("MonthlyResetTime"), $Target->getTimestamp());
    }
    protected function SetYearlyTimerInterval()
	{
        $Now = new DateTime(); 
		$Target = new DateTime(); 
		$Target->modify('1st January Next Year');
		$Target->setTime(0,0,10); 
		$Diff = $Target->getTimestamp() - $Now->getTimestamp(); 
		$Interval = $Diff * 1000;  
		$this->SetTimerInterval("YearlyReset", $Interval);
		SetValue($this->GetIDForIdent("YearlyResetTime"), $Target->getTimestamp());
    }
}
?>
