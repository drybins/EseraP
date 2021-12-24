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
        $this->RegisterPropertyInteger("AnnualLimit", 1000);
        $this->RegisterPropertyInteger("LimitActive", 100);
		
		$this->RegisterVariableInteger("Counter", "Counter", "", 1);
		$this->RegisterVariableFloat("Verbrauch", "Verbrauch", "~Gas", 2);
		
		$this->RegisterVariableInteger("TagCounter", "Counter Tag", "", 3);
		$this->RegisterVariableFloat("VerbrauchTagm", "Verbrauch am Tag in m³", "~Gas", 4);
		$this->RegisterVariableFloat("VerbrauchTagkwh", "Verbrauch am Tag in KWh", "Kirsch.KWh", 5);
		
		$this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounterG($_IPS[\'TARGET\']);'); 
		
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
        //$this->SetDailyTimerInterval();
        //$this->SetMonthlyTimerInterval();
        //$this->SetYearlyTimerInterval();    
    }

	public function ReceiveData($JSONString) 
	{
        // not implemented   
    }
	
	public function RefreshCounterG()
	{
       $this->calculate();   
    }
	
	private function Calculate()
	{
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
		$FactorKWh = $Factor * (0,9692*11,293);
		//SetValue($this->GetIDForIdent("VerbrauchTagKWh"), $CounterTag * $Factor * 0,9692 * 11,293);
		
		//$this->DebugMessage("Counter", "CounterOld: " . $CounterOld);
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
}
?>
