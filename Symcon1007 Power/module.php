<?
//******************************************************************************
//	Name		:	.ips.php
//	Aufruf		:	
//	Info		:	
//	Funktionen	:	
//
//******************************************************************************

// Klassendefinition
class Lights extends IPSModule 
	{
	//******************************************************************************
	// Der Konstruktor des Moduls
	// �berschreibt den Standard Kontruktor von IPS
	//******************************************************************************
	public function __construct($InstanceID) 
		{
		// Diese Zeile nicht loeschen
		parent::__construct($InstanceID);
 
		// Selbsterstellter Code
		}

	//******************************************************************************
	// �berschreibt die interne IPS_Create($id) Funktion	
	//****************************************************************************** 
	public function Create()
		{
		$this->RegisterPropertyInteger("PropertyInstanceID",0);
		$this->RegisterPropertyInteger("PropertyDeviceType",0);
		$this->RegisterPropertyBoolean("PropertyLogging",0);
		$this->RegisterPropertyString("PropertyPIRISetting",0);
    $this->RegisterPropertyString("PropertySzeneSetting",0);
		$this->RegisterPropertyInteger("PropertyTimeClock",0);

		$this->RegisterTimer("TimerUpdate",10000,'LGT_TimerUpdate($_IPS[\'TARGET\']);');

		$this->RegisterPropertyInteger("Property1", 0);
		$this->RegisterPropertyInteger("Property2", 0);
		$this->RegisterPropertyInteger("Property3", 0);
		$this->RegisterPropertyInteger("Property4", 0);
		$this->RegisterPropertyInteger("Property5", 0);
		$this->RegisterPropertyInteger("Property6", 0);
		$this->RegisterPropertyInteger("Property7", 0);
		$this->RegisterPropertyInteger("Property8", 0);
		$this->RegisterPropertyInteger("Property9", 0);
		$this->RegisterPropertyInteger("Property10", 0);
		$this->RegisterPropertyInteger("Property11", 0);
		$this->RegisterPropertyInteger("Property12", 0);

		$this->RegisterPropertyInteger('PropertyIsDayIndicatorID', 0);
		$this->RegisterPropertyInteger('PropertyBrightnessID', 0);
		$this->RegisterPropertyInteger('PropertyBrightnessAvgMinutes', 0);
		$this->RegisterPropertyInteger('PropertyBrightnessThresholdID', 0);

		$this->RegisterAttributeInteger("AttributTimeClock", 0);
		$this->RegisterAttributeInteger("AttributManuelMode", 0);
    $this->RegisterAttributeInteger("AttributAktivatedByPiri", 0);
    $this->RegisterAttributeInteger("AttributAktivatedByManual", 0);


		// Diese Zeile nicht l�schen.
		parent::Create();
 
		}
 
	//******************************************************************************
	// �berschreibt die intere IPS_ApplyChanges($id) Funktion	
	//******************************************************************************
	public function ApplyChanges()
		{

		$this->GetConfigurationForm();
		$this->CheckConfiguration();


		// Diese Zeile nicht l�schen
		parent::ApplyChanges();
		}
 
 	//******************************************************************************
 	//	
 	//******************************************************************************
	function CheckConfiguration()
		{

		$arrString = $this->ReadPropertyString("PropertyPIRISetting");
		$arr = json_decode($arrString,true);

		if ( $arrString == false )
			return;

		$this->DeleteUnusedMessages();
		
		// hier rein mit dem status der Lampe wegen manuellem schalten
		$AktorID = $this->ReadPropertyInteger("PropertyInstanceID");
		$StatusVariableID =  @IPS_GetObjectIDByIdent("StatusVariable",$AktorID);
		if ( $StatusVariableID == false )
			IPS_Logmessage("CheckConfiguration","IDENT: StatusVariable nicht gefunden fuer ".$AktorID);
		else
			$this->AddMessage($StatusVariableID);

		
		foreach( $arr as $device)
			{
			$statusID = $device['StatusvariableID'];

			if ( $statusID == 0 )
				continue;
			
			$output = $statusID;
			$this->SendDebug("CheckConfiguration",$output,0);
			$this->AddMessage($statusID);

			}

		$output = $arrString;
		$this->SendDebug("CheckConfiguration",$output,0);

		}


	//******************************************************************************
	//
	//******************************************************************************
	public function ActivateSzene($SzeneName,$StatusSzene=TRUE)
		{
		$output = "Aktiviere Szene : ". $SzeneName . " Status : ". $StatusSzene;
    $this->SendDebug("ActivateSzene",$output,0);

    $allLightInstances = IPS_GetInstanceListByModuleID('{FF72D8B0-3C5F-092B-8ABF-656C87CDF12D}');

		$count = 0;
		foreach($allLightInstances as $LightInstance)
			{
			//echo "\n".$count ."\n";
			$count = $count + 1;
      $output = "Suche Szene in " . $LightInstance;
    	$this->SendDebug("ActivateSzene",$output,0);

      //$arrString = $this->ReadPropertyString("PropertySzeneSetting");
			//$arr = json_decode($arrString,true);


      $arrString = IPS_GetConfiguration($LightInstance);
      $arr = json_decode($arrString,true);
			//print_r($arr);
			$devicetyp = $arr['PropertyDeviceType'];

			$arr = $arr['PropertySzeneSetting'];
      $arr = json_decode($arr,true);
			if ( $arr == false )
				continue;
      //print_r($arr);


			foreach ( $arr as $device )
				{
				$name = $device['SzenenName'];

      	$output = "Suche Szene ".$SzeneName. " gefunden " . $name;
    		//$this->SendDebug("ActivateSzene",$output,0);
				if ($SzeneName == $name )
					$this->SzeneFound($LightInstance,$SzeneName,$arr,$StatusSzene,$devicetyp);

				}


			}

		}

	//******************************************************************************
	//
	//******************************************************************************
	function SzeneFound($LightInstance,$SzeneName,$arr,$StatusSzene,$devicetyp)
		{
    $output = "[".$LightInstance."]".$SzeneName. " gefunden ";
    $this->SendDebug("SzeneFound",$output,0);
    print_r($arr);
		foreach( $arr as $device )
			{   echo "\n..." ;
			if ( $device['Status'] == false )
				continue;

     $time = $device['Zeit'];

	// 				$id = 0 AUS
	// 				$id = 1 Switch
	// 				$id = 2 Dimmer
	// 				$id = 3.RGB Intensitaet
	// 				$id = 4.RGB Farbe
	// 				$id = 5 RGB EIN
	// 				$id = 6 Dimmer EIN

      if ( $StatusSzene == false )
        $devicetyp = 0;

			if ( $devicetyp == 0 )  // Switch
				{
				$level = false ;
				LGT_DoAction($LightInstance,0,$level,$time);
				$time = 0;
				}

			if ( $devicetyp == 1 )  // Switch
				{
				$level = true ;
				LGT_DoAction($LightInstance,1,$level,$time);
				}

			if ( $devicetyp == 2 )  // Dimmer
				{
				$level = $device['Dimmer'] ;
				LGT_DoAction($LightInstance,1,$level,$time);
				}

			if ( $devicetyp == 3 )  // RGB
				{
				$level = $device['Dimmer'] ;
				LGT_DoAction($LightInstance,3,$level,$time);
				$level = $device['Farbe'] ;
				LGT_DoAction($LightInstance,4,$level,$time);

				}

			/*
			if ( $time > 0 )
				{
				$now = time();
				$timeclock = $now + $time;

				$output = "WriteAttributInteger:".date("d.m.Y H:i:s",time()). " bis ". date("d.m.Y H:i:s",$timeclock);
				$this->SendDebug("SzeneFound",$output,0);
				$this->WriteAttributeInteger ("AttributTimeClock", $timeclock);

				}
			*/


			}

		}

	//******************************************************************************
	//	Timer alle 10 Sekunden. Checken ob Zeit abgelaufen
	//******************************************************************************
	public function TimerUpdate()
		{
		$timeclock = $this->ReadAttributeInteger("AttributTimeClock");

		if ( $timeclock == 0 )	// Keine Zeit laeuft
			return;

		$output = "Timerupdate:".date("d.m.Y H:i:s",time()). " bis ". date("d.m.Y H:i:s",$timeclock);
		$this->SendDebug("Timerupdate",$output,0);

		$now = time();
		if ( $now > $timeclock )  // Ausschalten
			{
			$output = "Timerupdate abgelaufen";
			$this->SendDebug("Timerupdate",$output,0);

			$this->DoAction(0,false,0);
			$this->WriteAttributeInteger ("AttributTimeClock", 0);
			}

		}


	//******************************************************************************
	//	Wird aufgruefen wenn sich der Status eines PIRIs aendert
	//******************************************************************************
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
		{

		$status = $Data[0];	// Status der ueberwachten Variable
    $statusold = $Data[2];	// Status der ueberwachten Variable
		//print_r($Data);

		$output = "Data0:".$status." Data1:".$statusold;

    $this->SendDebug("MessageSink",$output,0);

    $AktorID = $this->ReadPropertyInteger("PropertyInstanceID");
		$StatusVariableID =  @IPS_GetObjectIDByIdent("StatusVariable",$AktorID);
		if ( $StatusVariableID == false )
			{
			IPS_Logmessage("CheckConfiguration","IDENT: StatusVariable nicht gefunden fuer ".$AktorID);
			return false;
			}

    // pruefen ob manuell geschaltet
    $timeclock = $this->ReadAttributeInteger("AttributTimeClock");

		// Status des Aktors
		if ( $StatusVariableID == $SenderID ) // geschaltet
			{
			$output = "ID: ".$SenderID;
				
			if ( $status == false ) // manuell/automatisch ausgeschaltet
				{
				$output = $output . " manuell/automatisch ausgeschaltet";
        $this->WriteAttributeInteger ("AttributManuelMode", 0);
				}
			else
				{
				if ( $timeclock == 0 )
					{
					$output = $output . " manuell eingeschaltet";
        	$this->WriteAttributeInteger ("AttributManuelMode", 1);
					}
				else
					{
          $output = $output . " manuell eingeschaltet waehrend PIRI-Zeit : " . $timeclock ;
        	$this->WriteAttributeInteger ("AttributManuelMode", 1);

					}
				}
					
			$this->SendDebug("MessageSink",$output,0);

			}

				

		if ( $status == true) // Bewegung erkannt
			{
			$arrString = $this->ReadPropertyString("PropertyPIRISetting");
			$arr = json_decode($arrString,true);

			foreach ( $arr as $device )
				{
				$status = $device['Status'];
				if ( $status == 0 )
						continue;

				$statusID = $device['StatusvariableID'];
				if ( $statusID == 0 )
					continue;
				if ( $statusID != $SenderID )
					continue;

				$AktorID = $this->ReadPropertyInteger("PropertyInstanceID");
				
				$DeviceTyp = $this->ReadPropertyInteger("PropertyDeviceType");
				if ( $DeviceTyp == 0 )
					continue;

				$tagID = $device['TagvariableID'];
				if ( $tagID == 0 )
					$tag = false;
				else
					$tag   = GetValue($tagID);

				$hellID = $device['HelligkeitsvariableID'];
				if ( $hellID == 0 )
					$hell = false;
				else
					$hell   = GetValue($hellID);

				$time = $device['Zeit'];
				$dimm = $device['Dimmer'];
				$color = $device['Farbe'];

				$this->DoBewegungDetected($DeviceTyp,$AktorID,$tag,$hell,$time,$dimm,$color);

				if ( $time > 0 )
					{
					//$this->SetTimerInterval("TimerUpdate", 5000);

					}
				}
			}
		}


	//******************************************************************************
	//	Registriere eine Message bei Aenderung vom Status ( 10603 )
	//******************************************************************************
	function AddMessage( int $id)
		{
		$output = "ID: ".$id;
		$this->SendDebug("AddMessage",$output,0);

		$this->RegisterMessage($id, 10603);
		}

	//******************************************************************************
	//	loesche alle registrierte Messages
	//******************************************************************************
	function DeleteUnusedMessages()
		{
		//Gibt ein Array der aktiven registrierten Messages wieder
		$MessageList = $this->GetMessageList();

		foreach($MessageList as $key => $Message)
			{
			foreach($Message as $typ)
				{
				$output = "ID:".$key. " - ".$typ;
				$this->SendDebug("DeleteUnusedMessages",$output,0);
				$this->UnregisterMessage($key, $typ);
				}
			}
		}

	//******************************************************************************
	//	Check alle registrierte Messages
	//******************************************************************************
	function CheckUsedMessages()
		{
		//Gibt ein Array der aktiven registrierten Messages wieder
		$MessageList = $this->GetMessageList();

		foreach($MessageList as $key => $Message)
			{
			foreach($Message as $typ)
				{
				$output = "ID:".$key. " - ".$typ;
				$this->SendDebug("CheckUsedMessages",$output,0);

				}
			}
		}

	//******************************************************************************
	// Bewegung erkannt	
	//******************************************************************************
	function DoBewegungDetected(int $DeviceTyp,int $instanceID,$tag,$hell,$time,$dimm,$color)
		{
		$output = $DeviceTyp. " - " .$instanceID." Tag:".$tag." Helligkeit:".$hell." Zeit:".$time." Dimmer:".$dimm." Farbe:".$color;
		$this->SendDebug("DoBewegungDetected",$output,0);

		if ( $tag == true )
			return;

		if ( $DeviceTyp == 2 )  // Dimmer
			{
			if ( $dimm == 100 )
				{
				$this->DoAction(2,100,0);
				//$this->DoAction(6,true,0);
				}
			else
				$this->DoAction(2,$dimm,0);

			}

		$now = time();
		$timeclock = $now + $time;

		$output = "WriteAttributInteger:".date("d.m.Y H:i:s",time()). " bis ". date("d.m.Y H:i:s",$timeclock);
		$this->SendDebug("DoBewegungDetected",$output,0);
		$this->WriteAttributeInteger ("AttributTimeClock", $timeclock);

		}

	//******************************************************************************
	//	DoAction 
	// 				$id = 0 AUS
	// 				$id = 1 Switch
	// 				$id = 2 Dimmer
	// 				$id = 3.RGB Intensitaet
	// 				$id = 4.RGB Farbe
	// 				$id = 5 RGB EIN
	// 				$id = 6 Dimmer EIN
	// 
	// 				$status = true,false,level,intensity,color
	//        $time   = Ausschaltzeit
	//******************************************************************************
	public function DoAction(int $id,int $status,int $outtime)
		{
		
		$output = "ID: ".$id." Status: ".$status;
		$this->SendDebug("DoAction",$output,0);
		$this->Logging("DoAction".$output);

		$AktorID = $this->ReadPropertyInteger("PropertyInstanceID");

		if ( $id == 1 or $id == 0)	// Switch
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("StatusVariable",$AktorID);
			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: StatusVariable nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

		if ( $id == 2 )	// Dimmer
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("IntensityVariable",$AktorID);
			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: IntensityVariable nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

		if ( $id == 3)	// RGB Intensitaet
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("BRIGHTNESS",$AktorID);
			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: BRIGHTNESS nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

		if ( $id == 4)	// RGB Farbe
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("COLOR",$AktorID);
			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: COLOR nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

		if ( $id == 5 or $id == 0)	// RGB Ein/Aus
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("STATE",$AktorID);
      if ( $StatusVariableID == false )
      	$StatusVariableID =  @IPS_GetObjectIDByIdent("StatusVariable",$AktorID);

			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: STATE/StatusVariable nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

		if ( $id == 6 or $id == 0 )	// Dimmer Ein/Aus
			{
			$StatusVariableID =  @IPS_GetObjectIDByIdent("StatusVariable",$AktorID);
			if ( $StatusVariableID == false )
				IPS_Logmessage("DoAction","IDENT: StatusVariable nicht gefunden fuer ".$AktorID);
			else
				RequestAction($StatusVariableID,$status);
			}

			if ( $id == 0 )
				$outtime = 0;

      if ( $outtime > 0 )
				{
				$now = time();
				$timeclock = $now + $outtime;

				$output = "WriteAttributInteger:".date("d.m.Y H:i:s",time()). " bis ". date("d.m.Y H:i:s",$timeclock);
				$this->SendDebug("SzeneFound",$output,0);
				$this->WriteAttributeInteger ("AttributTimeClock", $timeclock);
				}
			else
				{
      	$output = "WriteAttributInteger: 0";
				$this->SendDebug("SzeneFound",$output,0);
				$this->WriteAttributeInteger ("AttributTimeClock", 0);


				}

	}
	
	//******************************************************************************
	//	Test 
	//******************************************************************************	
	public function Test()
		{
		echo "Test";	
		}

	//**************************************************************************
	//  Logging
	//**************************************************************************
	private function Logging($Text)
		{
		if ( $this->ReadPropertyBoolean("PropertyLogging") == false )
			return;
			
		$ordner = IPS_GetLogDir() . "Lights";
		if ( !is_dir ( $ordner ) )
			mkdir($ordner);

		if ( !is_dir ( $ordner ) )
			return;

		$time = date("d.m.Y H:i:s");
		$logdatei = IPS_GetLogDir() . "Lights/Lights.log";
		$datei = fopen($logdatei,"a+");
		fwrite($datei, $time ." ". $Text . chr(13));
		fclose($datei);
		}


	//******************************************************************************
	//	Konfigurationsformular dynamisch erstellen
	//******************************************************************************
	public function GetConfigurationForm() 
		{
		$form = '
		
			{
			"elements":
				[
				{ "type": "Label"             , "label":  "####### Lights V 1.0 #######" },
				{ "type": "Button"            , "caption": "Dokumentation", "onClick": "echo 1 ;" },
				{ "type": "SelectInstance", "name": "PropertyInstanceID", "caption": "Target" },
				{ "type": "Select", "name": "PropertyDeviceType", "caption": "Typ",
					"options": 	
						[
						{ "caption": "Deaktiviert" 	, "value": 0 },
						{ "caption": "Schalter"		, "value": 1 },
						{ "caption": "Dimmer"		, "value": 2 },
						{ "caption": "RGB"			, "value": 3 }
						]
				},
				{ "type": "ExpansionPanel", "caption": "Bewegungsverwaltung",
					"items": 	
						[
        				{
    					"type": "List",
    					"name": "PropertyPIRISetting",
    					"caption": "",
    					"rowCount": 7,
    					"add": true,
    					"delete": true,
    					"sort": 
    						{
        					"column": "StatusvariableID",
        					"direction": "ascending"
    						},
    					"columns":	
    						[
                  			{
        					"caption": "Status",
        					"name": "Status",
        					"width": "50px",
        					"add": 0,
        					"edit":
								{
								"type": "CheckBox"
								}
							},
							{
        					"caption": "Statusvariable",
        					"name": "StatusvariableID",
        					"width": "auto",
        					"add": 0,
        					"edit":
								{
            					"type": "SelectVariable"
        						}
    						},
							{
        					"caption": "Tagvariable",
        					"name": "TagvariableID",
        					"width": "150px",
        					"add": 0,
                            "edit":
								{
            					"type": "SelectVariable"
        						}
    						},
               				{
        					"caption": "Helligkeitsvariable",
        					"name": "HelligkeitsvariableID",
        					"width": "150px",
        					"add": 0,
                            "edit":
								{
            					"type": "SelectVariable"
        						}
    						},
							{
        					"caption": "Zeit ( Sekunden )",
        					"name": "Zeit",
        					"width": "150px",
        					"add": 10,
        					"edit": 
        						{
            					"type": "NumberSpinner",
            					"digits": 0
        						}
    						},
                            {
        					"caption": "Dimmer Wert",
        					"name": "Dimmer",
        					"width": "150px",
        					"add": 100,
        					"edit": 
        						{
            					"type": "NumberSpinner",
            					"digits": 0
        						}
    						},
                            {
        					"caption": "Farb Wert",
        					"name": "Farbe",
        					"width": "150px",
        					"add": 0,
        					"edit": 
        						{
            					"type": "SelectColor"
            					}
    						}
							],
    					"values":
							[
							{
                            "StatusvariableID": 0,
                            "TagvariableID": 0,
                            "HelligkeitsvariableID": 0,
        					"Zeit": 10,
        					"Dimmer": 100,
        					"Farbe": "#FF0000",
        					"rowColor": "#C0C0C0"
    						}
							]
						}
					]
				},


				{ "type": "ExpansionPanel", "caption": "Szenenverwaltung",
					"items":
						[
        				{
    					"type": "List",
    					"name": "PropertySzeneSetting",
    					"caption": "",
    					"rowCount": 7,
    					"add": true,
    					"delete": true,
    					"sort":
    						{
        					"column": "SzenenName",
        					"direction": "ascending"
    						},
    					"columns":
    						[
                  			{
        					"caption": "Status",
        					"name": "Status",
        					"width": "50px",
        					"add": 0,
        					"edit":
								{
								"type": "CheckBox"
								}
							},
							{
        					"caption": "Szenen Name",
        					"name": "SzenenName",
        					"width": "auto",
        					"add": "Szene",
        					"edit":
								{
            					"type": "ValidationTextBox"
        						}
    						},
							{
        					"caption": "Tagvariable",
        					"name": "TagvariableID",
        					"width": "150px",
        					"add": 0,
                            "edit":
								{
            					"type": "SelectVariable"
        						}
    						},
               				{
        					"caption": "Helligkeitsvariable",
        					"name": "HelligkeitsvariableID",
        					"width": "150px",
        					"add": 0,
                            "edit":
								{
            					"type": "SelectVariable"
        						}
    						},
							{
        					"caption": "Zeit ( Sekunden )",
        					"name": "Zeit",
        					"width": "150px",
        					"add": 10,
        					"edit":
        						{
            					"type": "NumberSpinner",
            					"digits": 0
        						}
    						},
                            {
        					"caption": "Dimmer Wert",
        					"name": "Dimmer",
        					"width": "150px",
        					"add": 100,
        					"edit":
        						{
            					"type": "NumberSpinner",
            					"digits": 0
        						}
    						},
                            {
        					"caption": "Farb Wert",
        					"name": "Farbe",
        					"width": "150px",
        					"add": 0,
        					"edit":
        						{
            					"type": "SelectColor"
            					}
    						}
							],
    					"values":
							[
							{
                            "SzenenName": "Szene",
                            "TagvariableID": 0,
                            "HelligkeitsvariableID": 0,
        					"Zeit": 10,
        					"Dimmer": 100,
        					"Farbe": "#FF0000",
        					"rowColor": "#C0C0C0"
    						}
							]
						}
					]
				},



				{ "type": "CheckBox"          , "name" :  "PropertyLogging", "caption": "Logging (../logs/Lights/Lights.log)" } ,
				{ "type": "TestCenter" }

			],
  
			"actions":
				[
			';
	
		if ( $this->ReadPropertyInteger("PropertyDeviceType") == 1 )
			$form = $form . '
				{ "type": "RowLayout",
					"items": 
						[
						{ "type": "Label"             , "label":  "Schalter" },
						{ "type": "Button", "caption": "EIN", "onClick": "LGT_DoAction($id,1,true);" },
						{ "type": "Button", "caption": "AUS", "onClick": "LGT_DoAction($id,1,false);" }
						]
				}
				';
		
		if ( $this->ReadPropertyInteger("PropertyDeviceType") == 2 )
			$form = $form . '
				{ "type": "RowLayout",
					"items": 
						[
						{ "type": "Label"             , "label":  "Dimmer" },
						{ "type": "Button", "caption": "EIN", "onClick": "LGT_DoAction($id,6,true);" },
						{ "type": "Button", "caption": "AUS", "onClick": "LGT_DoAction($id,6,false);" },
						{ "type": "HorizontalSlider", "name": "SliderDimmer", "caption": "Dimmer", "minimum": 0, "maximum": 100, "onChange": "LGT_DoAction($id,2,$SliderDimmer);" }
						]
				}
				';

		if ( $this->ReadPropertyInteger("PropertyDeviceType") == 3 )
			$form = $form . '
				{ "type": "RowLayout",
					"items": 
						[
						{ "type": "Label"             , "label":  "RGB" },
						{ "type": "Button", "caption": "EIN", "onClick": "LGT_DoAction($id,5,true);" },
						{ "type": "Button", "caption": "AUS", "onClick": "LGT_DoAction($id,5,false);" },
						{ "type": "HorizontalSlider", "name": "SliderRGB", "caption": "Intensitaet", "minimum": 0, "maximum": 100, "onChange": "LGT_DoAction($id,3,$SliderRGB);" },
						{ "type": "SelectColor", "name": "HexColor", "caption": "Farbe"  },
						{ "type": "Button", "caption": "Farbe uebernehmen", "onClick": "LGT_DoAction($id,4,$HexColor);" }
						]
				}
				';

		$form = $form . '
			],
			"status":
    			[
        		{ "code": 101, "icon": "active", "caption": "Lights wird erstellt..." },
        		{ "code": 102, "icon": "active", "caption": "Lights ist aktiv" }
        		]
			}
			';
			
			
		return $form;	
		}

		
			
	}
?>