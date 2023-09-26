<?php

class Kuffer
{
	// Memory Arrays
	private $arrMemory = array();
	private $arrStack = array();

	// Registers
	private $A8 = [
		'id' => 'A8',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'accumulator',
		'value' => 0,
		'altvalue' => 0
	];

	private $B8 = [
		'id' => 'B8',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $C8 = [
		'id' => 'C8',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $D8 = [
		'id' => 'D8',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $E8 = [
		'id' => 'E8',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $A16 = [
		'id' => 'A16',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'accumulator',
		'value' => 0,
		'altvalue' => 0
	];

	private $B16 = [
		'id' => 'B16',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $C16 = [
		'id' => 'C16',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $D16 = [
		'id' => 'D16',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $E16 = [
		'id' => 'F16',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'general',
		'value' => 0,
		'altvalue' => 0
	];

	private $F = [
		'id' => 'F',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'flags',
		'value' => 0,
		'altvalue' => 0
	];

	private $T = [
		'id' => 'T',
		'bits' => 8,
		'canaddress' => false,
		'type' => 'register',
		'subtype' => 'flags',
		'value' => 0,
		'altvalue' => 0
	];

	private $PC = [
		'id' => 'PC',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'programcounter',
		'value' => 0,
		'altvalue' => 0
	];

	private $SP = [
		'id' => 'SP',
		'bits' => 16,
		'canaddress' => true,
		'type' => 'register',
		'subtype' => 'stackpointer',
		'value' => 0,
		'altvalue' => 0
	];

	// CPU Flag States
	private $NC = [
		'id' => 'NC',
		'type' => 'flagstate',
		'value' => 'NC'
	];

	private $C = [
		'id' => 'C',
		'type' => 'flagstate',
		'value' => 'C'
	];

	private $I = [
		'id' => 'I',
		'type' => 'flagstate',
		'value' => 'I'
	];

	private $NZ = [
		'id' => 'NZ',
		'type' => 'flagstate',
		'value' => 'NZ'
	];

	private $Z = [
		'id' => 'Z',
		'type' => 'flagstate',
		'value' => 'Z'
	];

	// Modifiers
	private function M($strSource_a)
	{
		$objResult = [
			'id' => 'MA',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'memoryaddress',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0,
			'source' => $strSource_a
		];

		if (is_int($strSource_a)) 
		{
			$objResult['value'] = $strSource_a;
		} 
		elseif (($strSource_a['type'] === 'register') && ($strSource_a['bits'] === 16)) 
		{
			$objResult['value'] = $strSource_a['value'];
		} 
		else 
		{
			$this->invalidMode('M', 'Invalid argument, must be a literal or 16 bit register.');
		}

		return $objResult;
	}

	private function H($strSource_a)
	{
		$objResult = [
			'id' => 'HB',
			'bits' => 8,
			'canaddress' => true,
			'type' => 'highbyte',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0,
			'source' => $strSource_a
		];

		if (is_int($strSource_a)) 
		{
			$objResult['value'] = $this->getHighByte($strSource_a);
		} 
		elseif (($strSource_a['type'] === 'register') && ($strSource_a['bits'] === 16)) 
		{
			$objResult['value'] = $this->getHighByte($strSource_a['value']);
		} 
		else 
		{
			$this->invalidMode('H', 'Invalid Argument, must be a literal or 16 bit register.');
		}

		return $objResult;
	}

	private function L($strSource_a)
	{
		$objResult = [
			'id' => 'LB',
			'bits' => 8,
			'canaddress' => true,
			'type' => 'lowbyte',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0,
			'source' => $strSource_a
		];

		if (is_int($strSource_a)) 
		{
			$objResult['value'] = $this->getLowByte($strSource_a);
		} 
		elseif (($strSource_a['type'] === 'register') && ($strSource_a['bits'] === 16)) 
		{
			$objResult['value'] = $this->getLowByte($strSource_a['value']);
		} 
		else 
		{
			$this->invalidMode('L', 'Invalid argument, must be a literal or 16 bit register.');
		}

		return $objResult;
	}

	// CPU Flags
	private $C = [
		'id' => 'C',
		'type' => 'flag',
		'value' => 0
	];

	private $I = [
		'id' => 'I',
		'type' => 'flag',
		'value' => 0
	];

	private $Z = [
		'id' => 'Z',
		'type' => 'flag',
		'value' => 0
	];

	// Methods
	public function __construct() 
	{
		$this->reset();
	}

	public function reset() 
	{
		$this->configureMemory();

		// initialise registers
		$this->A8 = [
			'id' => 'A8',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'accumulator',
			'value' => 0,
			'altvalue' => 0
		];

		$this->B8 = [
			'id' => 'B8',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->C8 = [
			'id' => 'C8',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->D8 = [
			'id' => 'D8',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->E8 = [
			'id' => 'E8',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->A16 = [
			'id' => 'A16',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'accumulator',
			'value' => 0,
			'altvalue' => 0
		];

		$this->B16 = [
			'id' => 'B16',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->C16 = [
			'id' => 'C16',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->D16 = [
			'id' => 'D16',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->E16 = [
			'id' => 'E16',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'general',
			'value' => 0,
			'altvalue' => 0
		];

		$this->F = [
			'id' => 'F',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'flags',
			'value' => 0,
			'altvalue' => 0
		];

		$this->T = [
			'id' => 'T',
			'bits' => 8,
			'canaddress' => false,
			'type' => 'register',
			'subtype' => 'flags',
			'value' => 0,
			'altvalue' => 0
		];

		$this->PC = [
			'id' => 'PC',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'programcounter',
			'value' => 0,
			'altvalue' => 0
		];

		$this->SP = [
			'id' => 'SP',
			'bits' => 16,
			'canaddress' => true,
			'type' => 'register',
			'subtype' => 'stackpointer',
			'value' => 0,
			'altvalue' => 0
		];

		$this->C = [
			'id' => 'C',
			'type' => 'flag',
			'value' => 0
		];

		$this->I = [
			'id' => 'I',
			'type' => 'flag',
			'value' => 0
		];

		$this->Z = [
			'id' => 'Z',
			'type' => 'flag',
			'value' => 0
		];
	}

	public function run($strInput_a)
	{
		// initialise memory
		$this->arrMemory = array();
		// ... memory initialization code here ...

		// create a UUID-based filename to store the input code
		$filename = __DIR__ . "/input_" . uniqid() . ".php";

		// write the input code to the file
		file_put_contents($filename, $strInput_a);

		// include the file
		include $filename;

		// delete the file
		unlink($filename);
	}

	private function instructionNotImplemented($strInstruction_a)
	{
		$strMessage = sprintf('Instruction not implemented: ' . $strInstruction_a);
		throw new Exception($strMessage);
	}

	private function invalidMode($strMode_a, $strReason_a) 
	{
		$strMessage = sprintf('Invalid mode %s. %s', $strMode_a, $strReason_a);
		throw new Exception($strMessage);
	}

	private function memoryException($strOperation_a, $intAddress_a)
	{
		$strMessage = sprintf('Memory %s error at address %s', $strOperation_a, $intAddress_a);
		throw new Exception($strMessage);
	}

	private function memoryInitialised($objMemory_a)
	{
		$this->consoleOutput("Memory '" . objMemory_a->id . "' initialised.");
	}

	private function consoleOutput($strOutput)
	{
		// do nothing
	}

	private function getLowByte($int16BitValue_a)
	{
		return $int16BitValue_a & 0xFF;
	}

	private function getHighByte($int16BitValue_a)
	{
		return ($int16BitValue_a >> 8) & 0xFF;
	}

	private function setLowByte($objL_a, $int8BitValue_a)
	{
		$intHigh = $this->getHighByte($objL_a['source']['value']);
		$intNewValue = ($intHigh << 8) + $int8BitValue_a;

		if (($objL_a['type'] === 'lowbyte') && ($objL_a['source']['type'] === 'register'))
		{
			$objL_a['value'] = $this->getLowByte($intNewValue);
			$objL_a['source']['value'] = $intNewValue;
		}
	}

	private function setHighByte($objH_a, $int8BitValue_a)
	{
		$intLow = $this->getLowByte($objH_a['source']['value']);
		$intNewValue = ($int8BitValue_a << 8) + $intLow;

		if (($objH_a['type'] === 'highbyte') && ($objH_a['source']['type'] === 'register'))
		{
			$objH_a['value'] = $this->getHighByte($intNewValue);
			$objH_a['source']['value'] = $intNewValue;
		}
	}

	private function readMemory8Bits($intAddress_a)
	{
		$intResult = null;

		$objMemory = getMemoryByAddressForRead($intAddress_a);
		if ($objMemory === null)
		{
			$this->memoryException('readMemory8Bits', $intAddress_a);
		}
		else
		{
			$intResult = $objMemory[$intAddress_a - $objMemory['startaddress']];
		}

		return $intResult;
	}

	private function readMemory16Bits($intAddress_a) 
	{
		$intAddress = $intAddress_a;

		$intLow = $this->readMemory8Bits($intAddress);
		$intAddress++;
		$intHigh = $this->readMemory8Bits($intAddress);

		return ($intHigh << 8) + $intLow;
	}

	private function writeMemory8Bits($intAddress_a, $int8BitValue_a) 
	{
		$objMemory = $this->getMemoryByAddressForWrite($intAddress_a);
		if ($objMemory === null) 
		{
			$this->memoryException('writeMemory8Bits', $intAddress_a);
		} 
		else 
		{
			$objMemory[$intAddress_a - $objMemory["startaddress"]] = $int8BitValue_a;
		}
	}

	private function writeMemory16Bits($intAddress_a, $int16BitValue_a)
	{
		$intLow = $this->getLowByte($int16BitValue_a);
		$intHigh = $this->getHighByte($int16BitValue_a);

		$intAddress = $intAddress_a;
		$this->writeMemory8Bits($intAddress, $intLow);
		$intAddress++;
		$this->writeMemory8Bits($intAddress, $intHigh);
	}

	private function getMemoryByAddressForWrite($intAddress_a) 
	{
		$objResult = null;
		
		// find a non-main address range that our address falls within, also skip ROM for write
		foreach ($this->arrMemory as $objMemory_a) 
		{
			if (($objMemory_a['id'] !== 'main') && ($objMemory_a['subtype'] !== 'rom') && ($objMemory_a['isenabled'])) 
			{
				if (($objMemory_a['startaddress'] <= $intAddress_a) && ($objMemory_a['endaddress'] >= $intAddress_a)) 
				{
					$this->initialiseMemory($objMemory_a);
					$objResult = $objMemory_a;
				}
			}
		}
		
		if ($objResult === null) 
		{
			// find a main address range that our address falls within
			foreach ($this->arrMemory as $objMemory_a) 
			{
				if (($objMemory_a['id'] === 'main') && ($objMemory_a['isenabled'])) 
				{
					if (($objMemory_a['startaddress'] <= $intAddress_a) && ($intAddress_a <= $objMemory_a['endaddress'])) 
					{
						$this->initialiseMemory($objMemory_a);
						$objResult = $objMemory_a;
					}
				}
			}
		}
		
		return $objResult;
	}

	private public function getMemoryByPort($intPort_a) 
	{
		$objResult = null;
		
		// find a non-main address range that our address falls within
		foreach ($this->arrMemory as $objMemory_a) 
		{
			if (($objMemory_a['enableport'] === $intPort_a) || ($objMemory_a['disableport'] === $intPort_a)) 
			{
				$this->initialiseMemory($objMemory_a);
				$objResult = $objMemory_a;
			}
		}
		
		return $objResult;
	}

	private function configureMemory()
	{
		$arrMemory = array();
		
		// initialise memory
		$arrMemory[] = array(
			'id' => 'main',
			'isenabled' => true,
			'isinitialised' => false,
			'enableport' => 1000,
			'disableport' => 1001,
			'startaddress' => 0,
			'endaddress' => 65535,
			'type' => 'memory',
			'subtype' => 'ram',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'lowerrom',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 2000,
			'disableport' => 2001,
			'startaddress' => 0,
			'endaddress' => 16384,
			'type' => 'memory',
			'subtype' => 'rom',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'upperrom',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 2002,
			'disableport' => 2003,
			'startaddress' => 49152,
			'endaddress' => 65535,
			'type' => 'memory',
			'subtype' => 'rom',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'bank5',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 3000,
			'disableport' => 3001,
			'startaddress' => 16384,
			'endaddress' => 32768,
			'type' => 'memory',
			'subtype' => 'ram',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'bank6',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 3002,
			'disableport' => 3003,
			'startaddress' => 16384,
			'endaddress' => 32768,
			'type' => 'memory',
			'subtype' => 'ram',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'bank7',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 3004,
			'disableport' => 3005,
			'startaddress' => 16384,
			'endaddress' => 32768,
			'type' => 'memory',
			'subtype' => 'ram',
			'content' => ''
		);
		
		$arrMemory[] = array(
			'id' => 'bank8',
			'isenabled' => false,
			'isinitialised' => false,
			'enableport' => 3006,
			'disableport' => 3007,
			'startaddress' => 16384,
			'endaddress' => 32768,
			'type' => 'memory',
			'subtype' => 'ram',
			'content' => ''
		);
	}
	
	private function initialiseMemory($objMemory_a)
	{
		if (!$objMemory_a['isinitialised'])
		{
			$intSize = $objMemory_a['endaddress'] - $objMemory_a['startaddress'];
			$objMemory_a['content'] = array_fill(0, $intSize, 0);
			$objMemory_a['isinitialised'] = true;
			$this->memoryInitialised($objMemory_a);
		}
	}

	// general register movement instructions

	// alt();
	private function alt()
	{
		$temp = $this->A8['value']; $this->A8['value'] = $this->A8['altvalue']; $this->A8['altvalue'] = $temp;
		$temp = $this->B8['value']; $this->B8['value'] = $this->B8['altvalue']; $this->B8['altvalue'] = $temp;
		$temp = $this->C8['value']; $this->C8['value'] = $this->C8['altvalue']; $this->C8['altvalue'] = $temp;
		$temp = $this->D8['value']; $this->D8['value'] = $this->D8['altvalue']; $this->D8['altvalue'] = $temp;
		$temp = $this->E8['value']; $this->E8['value'] = $this->E8['altvalue']; $this->E8['altvalue'] = $temp;
		
		$temp = $this->A16['value']; $this->A16['value'] = $this->A16['altvalue']; $this->A16['altvalue'] = $temp;
		$temp = $this->B16['value']; $this->B16['value'] = $this->B16['altvalue']; $this->B16['altvalue'] = $temp;
		$temp = $this->C16['value']; $this->C16['value'] = $this->C16['altvalue']; $this->C16['altvalue'] = $temp;
		$temp = $this->D16['value']; $this->D16['value'] = $this->D16['altvalue']; $this->D16['altvalue'] = $temp;
		$temp = $this->E16['value']; $this->E16['value'] = $this->E16['altvalue']; $this->E16['altvalue'] = $temp;

		$temp = $this->F['value']; $this->F['value'] = $this->F['altvalue']; $this->F['altvalue'] = $temp;
		//$temp = $this->M['value']; $this->M['value'] = $this->M['altvalue']; $this->M['altvalue'] = $temp;
	}

	// A:r8	-v		ld(<R8>, 		<value>);			ld a, 100
	// B:r8	-r8		ld(<R8>, 		<R8>);				ld a, b
	// C:r8	-mv		ld(<R8>, 		M(<value>));		ld a, (100)
	// D:r8	-mr16	ld(<R8>, 		M(<R16>));			ld a, (hl)
	// E:r8	-l		ld(<R8>, 		L(<R16>));			ld a, l
	// F:r8	-h		ld(<R8>, 		H(<R16>));			ld a, h

	// G:r16-v		ld(<R16>, 		<value>);			ld hl, 100
	// H:r16-f		ld(<R16>, 		<function>);		ld hl, function1
	// I:r16-r16	ld(<R16>, 		<R16>);				ld hl, de
	// J:r16-mv		ld(<R16>, 		M(<value>));		ld hl, (100)
	// K:r16-mr16	ld(<R16>, 		M(<R16>));			ld hl, (de)
	
	// L:mv	-r8		ld(M(<value>), 	<R8>);				ld (100), a
	// M:mv	-r16	ld(M(<value>), 	<R16>);				ld (100), hl
	// N:mv	-l		ld(M(<value>),	L(<R16>));			ld (100), l
	// O:mv	-h		ld(M(<value>), 	H(<R16>));			ld (100), h

	// P:mr16-r8	ld(M(<R16>), 	<R8>);				ld (hl), a 
	// Q:mr16-r16	ld(M(<R16>), 	<R16>);				ld (de), hl
	// R:mr16-l		ld(M(<R16>), 	L(<R16>));			ld (de), l
	// S:mr16-h		ld(M(<R16>), 	H(<R16>));			ld (de), h
	
	// T:l	-v		ld(L(<R16>), 	<value>);			ld l, 100
	// U:l	-r8		ld(L(<R16>), 	<R8>);				ld l, a
	// V:l	-mv		ld(L(<R16>), 	M(<value>));		ld l, (100)
	// W:l	-mr16	ld(L(<R16>), 	M(<R16>));			ld l, (de)

	// X:h	-v		ld(H(<R16>), 	<value>);			ld h, 100
	// Y:h	-r8		ld(H(<R16>), 	<R8>);				ld h, a
	// Z:h	-mv		ld(H(<R16>), 	M(<value>));		ld h, (100)
	// 0:h	-mr16	ld(H(<R16>), 	M(<R16>));			ld h, (de)

	// TODO: ld(PC, <R16>)
	// TODO: ld(<R16>, PC)
	// TODO: ld(SP, <R16>)
	// TODO: ld(<R16>, SP)
	private function ld($p1_a, $p2_a)
	{
		if ($p1_a['type'] === 'register')
		{
			// ASSIGN TO 8 BIT REGISTERS, SUCH AS A8 =
			if ($p1_a['bits'] === 8)
			{
				// 8 bit registers

				if (is_int($p2_a))
				{
					// A <value>
					$p1_a['value'] = $this->getLowByte($p2_a);
				}
				else if (($p2_a['type'] === 'register') && ($p2_a['bits'] === 8))
				{
					// B <R8>
					$p1_a['value'] = $p2_a['value'];
				}
				else if (($p2_a['type'] === 'memoryaddress') && (is_int($p2_a['source'])))
				{
					// C M(<value>)
					$p1_a['value'] = $this->readMemory8Bits($p2_a['source']);
				}
				else if (($p2_a['type'] === 'memoryaddress') && ($p2_a['source']['type'] === 'register'))
				{
					// D M(<R16>)
					$p1_a['value'] = $this->readMemory8Bits($p2_a['source']['value']);
				}
				else if ($p2_a['type'] === 'lowbyte')
				{
					// E L(<R16>)
					$p1_a['value'] = $p2_a['value'];
				}
				else if ($p2_a['type'] === 'highbyte')
				{
					// F H(<R16>)
					$p1_a['value'] = $p2_a['value'];
				}
			}
			// ASSIGN TO 16 BIT REGISTERS, SUCH AS A16 =
			else if ($p1_a['bits'] === 16)
			{
				// 16 bit registers

				if (is_int($p2_a))
				{
					// G <value>
					$p1_a['value'] = $p2_a;
				}
				else if (is_callable($p2_a))
				{
					// H <function>
					$p1_a['value'] = $p2_a;
				}
				else if (($p2_a['type'] === 'register') && ($p2_a['bits'] === 16))
				{
					// I <R16>
					$p1_a['value'] = $p2_a['value'];
				}
				else if (($p2_a['type'] === 'memoryaddress') && (is_int($p2_a['source'])))
				{
					// J M(<value>)
					$p1_a['value'] = $this->readMemory16Bits($p2_a['source']);
				}
				else if (($p2_a['type'] === 'memoryaddress') && ($p2_a['source']['type'] === 'register'))
				{
					// K M(<R16>)
					$p1_a['value'] = $this->readMemory16Bits($p2_a['source']['value']);
				}
			}
		}
		else if ($p1_a['type'] === 'memoryaddress') 
		{
			// ASSIGN TO LITERAL MEMORY ADDRESS, SUCH AS M(0) =
			if (is_int($p1_a['source'])) 
			{
				if ($p2_a['type'] === 'register' && $p2_a['bits'] === 8) 
				{
					// L <R8>
					$this->writeMemory8Bits($p1_a['source'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'register' && $p2_a['bits'] === 16) 
				{
					// M <R16>
					$this->writeMemory16Bits($p1_a['source'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'lowbyte') 
				{
					// N L(<R16>)
					$this->writeMemory8Bits($p1_a['source'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'highbyte') 
				{
					// O H(<R16>)
					$this->writeMemory8Bits($p1_a['source'], $p2_a['value']);
				}
			}
			// ASSIGN TO MEMORY ADDRESS POINTED TO BY REGISTER, SUCH AS M(A16) =
			else if ($p1_a['source']['type'] === 'register' && $p1_a['source']['bits'] === 16) 
			{
				if ($p2_a['type'] === 'register' && $p2_a['bits'] === 8) 
				{
					// P <R8>
					$this->writeMemory8Bits($p1_a['source']['value'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'register' && $p2_a['bits'] === 16) 
				{
					// Q <R16>
					$this->writeMemory16Bits($p1_a['source']['value'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'lowbyte') 
				{
					// R L(<R16>)
					$this->writeMemory16Bits($p1_a['source']['value'], $p2_a['value']);
				} 
				else if ($p2_a['type'] === 'highbyte') 
				{
					// S H(<R16>)
					$this->writeMemory16Bits($p1_a['source']['value'], $p2_a['value']);
				}
			} 
			else 
			{
				$this->invalidMode('ld', 'Invalid load into memory address.');
			}
		}
		// ASSIGN TO HIGH BYTES, SUCH AS H(A16) =
		else if ($p1_a['type'] === 'highbyte')
		{
			// all 8 bits
			if (is_int($p2_a))
			{
				// T <value>
				$this->setHighByte($p1_a, $p2_a);
			}
			else if (($p2_a['type'] === 'register') && ($p2_a['bits'] === 8))
			{
				// U <R8>
				$this->setHighByte($p1_a, $p2_a['value']);
			}
			else if (($p2_a['type'] === 'memoryaddress') && (is_int($p2_a['source'])))
			{
				// V M(<value>)
				$this->setHighByte($p1_a, $this->readMemory8Bits($p2_a['source']));
			}
			else if (($p2_a['type'] === 'memoryaddress') && ($p2_a['source']['type'] === 'register'))
			{
				// W M(<R16>)
				$this->setHighByte($p1_a, $this->readMemory8Bits($p2_a['source']['value']));
			}
		}
		// ASSIGN TO LOW BYTES, SUCH AS L(A16) =
		else if ($p1_a['type'] === 'lowbyte')
		{
			// all 8 bits
			if (is_int($p2_a))
			{
				// X <value>
				$this->setLowByte($p1_a, $p2_a);
			}
			else if (($p2_a['type'] === 'register') && ($p2_a['bits'] === 8))
			{
				// Y <R8>
				$this->setLowByte($p1_a, $p2_a['value']);
			}
			else if (($p2_a['type'] === 'memoryaddress') && (is_int($p2_a['source'])))
			{
				// Z M(<value>)
				$this->setLowByte($p1_a, $this->readMemory8Bits($p2_a['source']));
			}
			else if (($p2_a['type'] === 'memoryaddress') && ($p2_a['source']['type'] === 'register'))
			{
				// 0 M(<R16>)
				$this->setLowByte($p1_a, $this->readMemory8Bits($p2_a['source']['value']));
			}
		}
		else
		{
			$this->invalidMode('ld', 'Invalid load into memory address.');
		}
	}

	private function nop() { }
	
	// pop(<R8>);
	// pop(<R16>);
	private function pop(&$objReg_a)
	{
		$objReg_a['value'] = array_pop($this->arrStack);
	}

	// push(<R8>);
	// push(<R16>);
	private function push($objReg_a)
	{
		array_push($this->arrStack, $objReg_a->value);
	}

	// swap(<R8>, <R8>);
	// swap(<R16>, <R16>);
	private function swap(&$objReg1_a, &$objReg2_a)
	{
		$temp = null;
		
		if ($objReg1_a['bits'] === $objReg2_a['bits'])
		{
			$temp = $objReg1_a['value'];
			$objReg1_a['value'] = $objReg2_a['altvalue'];
			$objReg2_a['altvalue'] = $temp;
		}
		else
		{
			$this->invalidMode('swap', 'Cannot swap registers of different sizes.');
		}
	}
	
	// flow control instructions
	
	// call(<function>);
	// call(<R16>);
	// call(C, <function>);
	// call(NC, <function>);
	// call(NZ, <function>);
	// call(Z, <function>);
	// call(C, <R16>);
	// call(NC, <R16>);
	// call(NZ, <R16>);
	// call(Z, <R16>);
	private function call($objFlagState_a, $cb_a)
	{
		$cb = $cb_a;

		if ($cb === null)
		{
			// unconditional call
			$cb = $objFlagState_a;

			if (is_callable($cb))
			{
				$cb();
			}
			else if (($cb['type'] === 'register') && ($cb['canaddress']) && (is_callable($cb['value'])))
			{
				$cb['value']();
			}
		}
		else
		{
			if (($cb['type'] === 'register') && ($cb['canaddress']) && (is_callable($cb['value'])))
			{
				// conditional call
				if (($objFlagState_a['id'] === 'NC') && ($this->flagC->value === 0))
				{
					$cb['value']();
				}
				else if (($objFlagState_a['id'] === 'C') && ($this->flagC->value === 1))
				{
					$cb['value']();
				}
				else if (($objFlagState_a['id'] === 'NZ') && ($this->flagZ->value === 0))
				{
					$cb['value']();
				}
				else if (($objFlagState_a['id'] === 'Z') && ($this->flagZ->value === 1))
				{
					$cb['value']();
				}
			}
			else
			{
				// conditional call
				if (($objFlagState_a['id'] === 'NC') && ($this->flagC->value === 0))
				{
					$cb();
				}
				else if (($objFlagState_a['id'] === 'C') && ($this->flagC->value === 1))
				{
					$cb();
				}
				else if (($objFlagState_a['id'] === 'NZ') && ($this->flagZ->value === 0))
				{
					$cb();
				}
				else if (($objFlagState_a['id'] === 'Z') && ($this->flagZ->value === 1))
				{
					$cb();
				}
			}
		}
	}

	// if(test(C)) { }
	// if(test(NC)) { }
	// if(test(NZ)) { }
	// if(test(Z)) { }
	// while(test(C)) { }
	// while(test(NC)) { }
	// while(test(NZ)) { }
	// while(test(Z)) { }
	// TODO: test(<bit>, <R8>)
	// TODO: test(<bit>, M(<R16>))
	private function test($objFlagState_a)
	{
		$blnResult = false;

		if (($objFlagState_a['id'] === 'NC') && ($this->flagC->value === 0))
		{
			$blnResult = true;
		}
		else if (($objFlagState_a['id'] === 'C') && ($this->flagC->value === 1))
		{
			$blnResult = true;
		}
		else if (($objFlagState_a['id'] === 'NZ') && ($this->flagZ->value === 0))
		{
			$blnResult = true;
		}
		else if (($objFlagState_a['id'] === 'Z') && ($this->flagZ->value === 1))
		{
			$blnResult = true;
		}

		return $blnResult;
	}

	// arithmetic instructions
	
	private function adc($objReg_a, $source_a)
	{
		$blnCarry = $this->test($this->C);

		$this->set($this->Z, 0);
		$this->set($this->C, 0);

		if (is_int($source_a))
		{
			$objReg_a->value = $objReg_a->value + $source_a;
		}
		else if ($source_a['type'] === 'register')
		{
			if ($source_a['bits'] == $objReg_a->bits)
			{
				$objReg_a->value = $objReg_a->value + $source_a['value'];
			}
			else if (is_callable($objReg_a->value))
			{
				$this->invalidMode('adc', 'Cannot add a value to a function call.');
			}
			else if (is_callable($source_a['value']))
			{
				$this->invalidMode('adc', 'Cannot add a function call.');
			}
			else
			{
				$this->invalidMode('adc', 'More information is unavailable.');
			}
		}
		else
		{
			$this->invalidMode('adc', 'You can only add a value or a register.');
		}

		if ($blnCarry)
		{
			$objReg_a->value++;
		}

		if ($objReg_a->bits === 8)
		{
			if ($objReg_a->value > 255)
			{
				$objReg_a->value = $objReg_a->value % 256;
				$this->set($this->C, 1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($objReg_a->value > 65535)
			{
				$objReg_a->value = $objReg_a->value % 65536;
				$this->set($this->C, 1);
			}
		}

		if ($objReg_a->value === 0)
		{
			$this->set($this->Z, 1);
		}
	}
	
	// add(<R8>, <value>);
	// add(<R8>, <R8>);
	// add(<R16>, <value>);
	// add(<R16>, <R16>);
	// TODO: add(M(<R16>), <value>);
	// TODO: add(<R16>, M(<R16>));
	// TODO: add(M(<R16>), <R16>);
	private function add($objReg_a, $source_a)
	{
		$this->setZ(0);
		$this->setC(0);
			
		if (is_int($source_a))
		{
			$objReg_a->value = $objReg_a->value + $source_a;
		}
		else if (($source_a['type'] === 'register') && ($source_a['bits'] === $objReg_a->bits))
		{
			$objReg_a->value = $objReg_a->value + $source_a['value'];
		}
		else if (is_callable($objReg_a->value))
		{
			$this->invalidMode('add', 'Cannot add a value to a function call.');
		}
		else if (($source_a['type'] === 'register') && (is_callable($source_a['value'])))
		{
			$this->invalidMode('add', 'Cannot add a function call.');
		}
		else
		{
			$this->invalidMode('add', 'More information is unavailable.');
		}

		if ($objReg_a->bits === 8)
		{
			if ($objReg_a->value > 255)
			{
				$objReg_a->value = $objReg_a->value % 256;
				$this->setC(1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($objReg_a->value > 65535)
			{
				$objReg_a->value = $objReg_a->value % 65536;
				$this->setC(1);
			}
		}

		if ($objReg_a->value === 0)
		{
			$this->setZ(1);
		}
	}
	
	// cp(<R8>, <value>);
	// cp(<R8>, <R8>);
	// cp(<R16>, <value>);
	// cp(<R16>, <R16>);
	// TODO: cp(M(<R16>), <value>);
	// TODO: cp(<R16>, M(<R16>));
	// TODO: cp(M(<R16>), <R16>);
	function cp($objReg_a, $source_a)
	{
		$intValue = $objReg_a->value;

		set($this->Z 0);
		set($this->C, 0);

		if (is_int($source_a))
		{
			$intValue = $intValue - $source_a;
		}
		else if (($source_a['type'] === 'register') && ($source_a['bits'] == $objReg_a->bits))
		{
			$intValue = $intValue - $source_a['value'];
		}
		else if (is_callable($objReg_a->value))
		{
			$this->invalidMode('cp', 'Cannot compare a value to a function call.');
		}
		else if (($source_a['type'] === 'register') && (is_callable($source_a['value'])))
		{
			$this->invalidMode('cp', 'Cannot compare a function call.');
		}
		else
		{
			$this->invalidMode('cp', 'You can only compare a value or a register.');
		}

		if ($objReg_a->bits === 8)
		{
			if ($intValue < 0)
			{
				$intValue = 256 + $intValue;
				set($this->C 1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($intValue < 0)
			{
				$intValue = 65536 + $intValue;
				set($this->C 1);
			}
		}

		if ($intValue === 0)
		{
			set($this->Z 1);
		}
	}

	// dec(R8);
	// dec(R16);
	// dec(M(<R16>));
	private function dec($objReg_a)
	{
		if (($objReg_a['type'] === 'memoryaddress') && ($objReg_a['source']['type'] === 'register'))
		{
			$this->ld($this->T8, $objReg_a);
			$this->dec($this->T8);
			$this->ld($objReg_a, $this->T8);
		}
		else
		{
			$objReg_a['value']--;
			$this->flagZ->value = 0;

			if ($objReg_a['value'] === -1)
			{
				if ($objReg_a['bits'] === 8)
				{
					$objReg_a['value'] = 255;
				}
				else if ($objReg_a['bits'] === 16)
				{
					$objReg_a['value'] = 65535;
				}
			}

			if ($objReg_a['value'] === 0)
			{
				$this->flagZ->value = 1;
			}
		}
	}
	
	// decm(value);
	// decm(R16);
	// function decm(objReg_a)
	// {
		// ld(T8, M(objReg_a));
		// dec(T8);
		// ld(M(objReg_a), T8);
	// }
	
	// div(<R8>, <value>);
	// div(<R8>, <R8>);
	// div(<R16>, <value>);
	// div(<R16>, <R16>);
	// TODO: div(M(<R16>), <value>);
	// TODO: div(<R16>, M(<R16>));
	// TODO: div(M(<R16>), <R16>);
	private function div($objReg_a, $source_a)
	{
		$this->setFlag($this->Z, 0);
		$this->setFlag($this->C, 0);

		if (is_int($source_a))
		{
			$objReg_a->value = intval($objReg_a->value / $source_a);
		}
		else if ($source_a['type'] === 'register')
		{
			if ($source_a['bits'] == $objReg_a->bits)
			{
				$objReg_a->value = intval($objReg_a->value / $source_a['value']);
			}
			else if (is_callable($objReg_a->value))
			{
				$this->invalidMode('div', 'Cannot divide a value to a function call.');
			}
			else if (is_callable($source_a['value']))
			{
				$this->invalidMode('div', 'Cannot divide a function call.');
			}
			else
			{
				$this->invalidMode('div', 'More information is unavailable.');
			}
		}
		else
		{
			$this->invalidMode('div', 'You can only divide a value or a register.');
		}

		if ($objReg_a->bits === 8)
		{
			if ($objReg_a->value < 0)
			{
				$objReg_a->value = 256 + $objReg_a->value;
				$this->setFlag($this->C, 1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($objReg_a->value < 0)
			{
				$objReg_a->value = 65536 + $objReg_a->value;
				$this->setFlag($this->C, 1);
			}
		}

		if ($objReg_a->value === 0)
		{
			$this->setFlag($this->Z, 1);
		}
	}
	
	// inc(R8);
	// inc(R16);
	// inc(M(<R16>));
	private function inc($objReg_a)
	{
		if (($objReg_a['type'] === 'memoryaddress') && ($objReg_a['source']['type'] === 'register'))
		{
			$T8 = new Register(8);
			$this->ld($T8, $objReg_a);
			$this->inc($T8);
			$this->ld($objReg_a, $T8);
		}
		else
		{
			$objReg_a['value']++;
			$this->flagZ->value = 0;

			if (((int)$objReg_a['bits'] === 8 && (int)$objReg_a['value'] === 256) || ((int)$objReg_a['bits'] === 16 && (int)$objReg_a['value'] === 65536))
			{
				$objReg_a['value'] = 0;
				$this->flagZ->value = 1;
			}
		}
	}

	// incm(value);
	// incm(R16);
	// function incm(objReg_a)
	// {
		// ld(T8, M(objReg_a));
		// inc(T8);
		// ld(M(objReg_a), T8);
	// }
	
	// sbc(<R8>, <value>);
	// sbc(<R8>, <R8>);
	// sbc(<R16>, <value>);
	// sbc(<R16>, <R16>);
	// TODO: sbc(M(<R16>), <value>);
	// TODO: sbc(<R16>, M(<R16>));
	// TODO: sbc(M(<R16>), <R16>);
	private function sbc($objReg_a, $source_a)
	{
		$blnCarry = $this->test($this->C);

		$this->set($this->Z, 0);
		$this->set($this->C, 0);

		if (is_int($source_a))
		{
			$objReg_a->value -= $source_a;
		}
		else if ($source_a->type === 'register')
		{
			if ($source_a->bits == $objReg_a->bits)
			{
				$objReg_a->value -= $source_a->value;
			}
			else if (is_callable($objReg_a->value))
			{
				$this->invalidMode('sbc', 'Cannot subtract a value to a function call.');
			}
			else if (is_callable($source_a->value))
			{
				$this->invalidMode('sbc', 'Cannot subtract a function call.');
			}
			else
			{
				$this->invalidMode('sbc', 'More information is unavailable.');
			}
		}
		else
		{
			$this->invalidMode('sbc', 'You can only subtract a value or a register.');
		}

		if ($blnCarry)
		{
			$objReg_a->value--;
		}

		if ($objReg_a->bits === 8)
		{
			if ($objReg_a->value < 0)
			{
				$objReg_a->value = 256 + $objReg_a->value;
				$this->set($this->C, 1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($objReg_a->value < 0)
			{
				$objReg_a->value = 65536 + $objReg_a->value;
				$this->set($this->C, 1);
			}
		}


		if ($objReg_a->value === 0)
		{
			$this->set($this->Z, 1);
		}
	}

	// mod(<R8>, <value>);
	// mod(<R8>, <R8>);
	// mod(<R16>, <value>);
	// mod(<R16>, <R16>);
	// TODO: mod(M(<R16>), <value>);
	// TODO: mod(<R16>, M(<R16>));
	// TODO: mod(M(<R16>), <R16>);
	private function mod($objReg_a, $source_a) 
	{
		$this->set($this->Z, 0);
		$this->set($this->C, 0);

		if (is_int($source_a)) {
			$objReg_a->value = intval($objReg_a->value % $source_a, 10);
		}
		elseif ($source_a->type === 'register') 
		{
			if ($source_a->bits == $objReg_a->bits) 
			{
				$objReg_a->value = intval($objReg_a->value % $source_a->value, 10);
			}
			elseif (is_callable($objReg_a->value)) 
			{
				$this->invalidMode('mod', 'Cannot calculate remainder of a function call.');
			}
			elseif (is_callable($source_a->value)) 
			{
				$this->invalidMode('mod', 'Cannot calculate remainder of a function call.');
			}
			else 
			{
				$this->invalidMode('mod', 'More information is unavailable.');
			}
		}
		else 
		{
			$this->invalidMode('mod', 'You can only calculate remainder of a value or a register.');
		}

		if ($objReg_a->bits === 8) 
		{
			if ($objReg_a->value < 0) 
			{
				$objReg_a->value = 256 + $objReg_a->value;
				$this->set($this->C, 1);
			}
		}
		elseif ($objReg_a->bits === 16) 
		{
			if ($objReg_a->value < 0) 
			{
				$objReg_a->value = 65536 + $objReg_a->value;
				$this->set($this->C, 1);
			}
		}

		if ($objReg_a->value === 0) 
		{
			$this->set($this->Z, 1);
		}
	}
	
	// mult(<R8>, <value>);
	// mult(<R8>, <R8>);
	// mult(<R16>, <value>);
	// mult(<R16>, <R16>);
	// TODO: mult(M(<R16>), <value>);
	// TODO: mult(<R16>, M(<R16>));
	// TODO: mult(M(<R16>), <R16>);
	private function mult($objReg_a, $source_a)
	{
		$this->set($this->Z, 0);
		$this->set($this->C, 0);
		
		if (is_int($source_a))
		{
			$objReg_a->value = $objReg_a->value * $source_a;
		}
		else if ($source_a->type === 'register')
		{
			if ($source_a->bits == $objReg_a->bits)
			{
				$objReg_a->value = $objReg_a->value * $source_a->value;
			}
			else if (is_callable($objReg_a->value))
			{
				$this->invalidMode('mult', 'Cannot multiply a value to a function call.');
			}
			else if (is_callable($source_a->value))
			{
				$this->invalidMode('mult', 'Cannot multiply a function call.');
			}
			else
			{
				$this->invalidMode('mult', 'More information is unavailable.');
			}
		}
		else
		{
			$this->invalidMode('mult', 'You can only multiply a value or a register.');
		}

		if ($objReg_a->bits === 8)
		{
			if ($objReg_a->value > 255)
			{
				$objReg_a->value = $objReg_a->value % 256;
				$this->set($this->C, 1);
			}
		}
		else if ($objReg_a->bits === 16)
		{
			if ($objReg_a->value > 65535)
			{
				$objReg_a->value = $objReg_a->value % 65536;
				$this->set($this->C, 1);
			}
		}

		if ($objReg_a->value === 0)
		{
			$this->set($this->Z, 1);
		}
	}
	
	// sub(<R8>, <value>);
	// sub(<R8>, <R8>);
	// sub(<R16>, <value>);
	// sub(<R16>, <R16>);
	// TODO: sub(M(<R16>), <value>);
	// TODO: sub(<R16>, M(<R16>));
	// TODO: sub(M(<R16>), <R16>);
	private function sub($objReg_a, $source_a)
	{
		$this->set($this->Z 0);
		$this->set($this->C 0);

		if (is_int($source_a)) 
		{
			$objReg_a->value = $objReg_a->value - $source_a;
		} 
		elseif ($source_a->type === 'register') 
		{
			if ($source_a->bits == $objReg_a->bits) 
			{
				$objReg_a->value = $objReg_a->value - $source_a->value;
			} 
			elseif (is_callable($objReg_a->value)) 
			{
				$this->invalidMode('sub', 'Cannot subtract a value to a function call.');
			} 
			elseif (is_callable($source_a->value)) 
			{
				$this->invalidMode('sub', 'Cannot subtract a function call.');
			} 
			else 
			{
				$this->invalidMode('sub', 'More information is unavailable.');
			}
		} 
		else 
		{
			$this->invalidMode('sub', 'You can only subtract a value or a register.');
		}

		if ($objReg_a->bits === 8) 
		{
			if ($objReg_a->value < 0) 
			{
				$objReg_a->value = 256 + $objReg_a->value;
				$this->set($this->C 1);
			}
		} 
		elseif ($objReg_a->bits === 16) 
		{
			if ($objReg_a->value < 0) 
			{
				$objReg_a->value = 65536 + $objReg_a->value;
				$this->set($this->C 1);
			}
		}

		if ($objReg_a->value === 0) 
		{
			$this->set($this->Z, 1);
		}
	}

	// input / output instructions
	
	private function input()
	{
		$this->instructionNotImplemented('input');
	}
	
	// output(<value>, <value>);
	// output(<R8>, <value>);
	// output(<R8>, <R8>);
	// output(<R16>, <value>);
	// output(<R16>, <R8>);
	private function output($port_a, $source_a)
	{
		$intPort = 0;
		
		if (is_int($port_a))
		{
			$intPort = $port_a;
		}
		else if ($port_a->type === 'register')
		{
			if (is_int($port_a->value))
			{
				$intPort = $port_a->value;
			}
			else
			{
				$this->invalidMode('output', 'The port must be a number or a register.');
			}
		}
		
		if ($intPort === CONSOLEOUTPORT)
		{
			if (is_int($source_a))
			{
				$this->consoleOutput($source_a);
			}
			else if ($source_a->type === 'register')
			{
				if ($source_a->bits === 8)
				{
					if (is_int($source_a->value))
					{
						$this->consoleOutput($source_a->value);
					}
					else
					{
						$this->invalidMode('output', 'Can only output a value or a register.');
					}
				}
				else
				{
					$this->invalidMode('output', 'Can only output 8 bits at a time.');
				}
			}
		}
	}
	
	// bit instructions
	
	// and(<R8>, <value>);
	// and(<R8>, <R8>);
	// and(<R16>, <value>);
	// and(<R16>, <R16>);
	// TODO: and(M(<R16>), <value>);
	// TODO: and(<R16>, M(<R16>));
	// TODO: and(M(<R16>), <R16>);
	private function and($objReg_a, $source_a)
	{
		if ($objReg_a['type'] === 'register') 
		{
			if ($objReg_a['bits'] === 8) 
			{
				// 8 bit registers
				if (is_int($source_a)) 
				{
					$objReg_a['value'] = $objReg_a['value'] & $this->getLowByte($source_a);
				} 
				else if (($source_a['type'] === 'register') && ($source_a['bits'] === 8)) 
				{
					$objReg_a['value'] = $objReg_a['value'] & $source_a['value'];
				}
			} 
			else if ($objReg_a['bits'] === 16) 
			{
				// 16 bit registers
				if (is_int($source_a)) 
				{
					$objReg_a['value'] = $objReg_a['value'] & $source_a;
				} 
				else if (($source_a['type'] === 'register') && ($source_a['bits'] === 8)) 
				{
					$objReg_a['value'] = $objReg_a['value'] & $source_a['value'];
				}
			}
			
			if ($objReg_a['value'] === 0) 
			{
				set($this->Z, 1);
			} 
			else 
			{
				set($this->Z, 0);
			}
		} 
		else 
		{
			invalidMode('and', 'Can only AND against a register.');
		}
	}
	
	// or(<R8>, <value>);
	// or(<R8>, <R8>);
	// or(<R16>, <value>);
	// or(<R16>, <R16>);
	// TODO: or(M(<R16>), <value>);
	// TODO: or(<R16>, M(<R16>));
	// TODO: or(M(<R16>), <R16>);
	private function or($objReg_a, $source_a) 
	{
		if ($objReg_a->type === 'register') 
		{
			if ($objReg_a->bits === 8) 
			{
				// 8 bit registers
				if (is_int($source_a)) 
				{
					$objReg_a->value = $objReg_a->value | $this->getLowByte($source_a);
				} 
				else if (($source_a->type === 'register') && ($source_a->bits === 8)) 
				{
					$objReg_a->value = $objReg_a->value | $source_a->value;
				}
			} 
			else if ($objReg_a->bits === 16) 
			{
				// 16 bit registers
				if (is_int($source_a)) 
				{
					$objReg_a->value = $objReg_a->value | $source_a;
				} 
				else if (($source_a->type === 'register') && ($source_a->bits === 8)) 
				{
					$objReg_a->value = $objReg_a->value | $source_a->value;
				}
			}

			if ($objReg_a->value === 0) 
			{
				$this->set($this->Z, 1);
			} 
			else 
			{
				$this->set($this->Z, 0);
			}
		} 
		else 
		{
			$this->invalidMode('or', 'Can only OR against a register.');
		}
	}
	
	// TODO: rl(<R8>)
	// TODO: rl(<R16>)
	// TODO: rl(M(<R16>))
	function rl()
	{
		$this->instructionNotImplemented('rl');
	}
	
	// TODO: rr(<R8>)
	// TODO: rr(<R16>)
	// TODO: rr(M(<R16>))
	function rr()
	{
		$this->instructionNotImplemented('rr');
	}
	
	// set(C, <value>);
	// set(I, <value>);
	// set(Z, <value>);
	// TODO: set(<bit>, <R8>, <value>)
	// TODO: set(<bit>, M(<R16>), <value>)
	private function set($objFlagState_a, $intValue_a)
	{
		if ($objFlagState_a->id === 'C')
		{
			$this->flagC->value = $intValue_a;
		}
		else if ($objFlagState_a->id === 'I')
		{
			$this->flagI->value = $intValue_a;
		}
		else if ($objFlagState_a->id === 'Z')
		{
			$this->flagZ->value = $intValue_a;
		}
	}

	// TODO: sl(<R8>)
	// TODO: sl(<R16>)
	// TODO: sl(M(<R16>))
	function sl()
	{
		$this->instructionNotImplemented('sl');
	}
	
	// TODO: sr(<R8>)
	// TODO: sr(<R16>)
	// TODO: sr(M(<R16>))
	function sr()
	{
		$this->instructionNotImplemented('sr');
	}
	
	// xor(<R8>, <value>);
	// xor(<R8>, <R8>);
	// xor(<R16>, <value>);
	// xor(<R16>, <R16>);
	// TODO: xor(M(<R16>), <value>);
	// TODO: xor(<R16>, M(<R16>));
	// TODO: xor(M(<R16>), <R16>);
	private function xor($objReg_a, $source_a)
	{
		if ($objReg_a['type'] === 'register')
		{
			if ($objReg_a['bits'] === 8)
			{
				// 8 bit registers

				if (is_int($source_a))
				{
					$objReg_a['value'] = $objReg_a['value'] ^ $this->getLowByte($source_a);
				}
				else if (($source_a['type'] === 'register') && ($source_a['bits'] === 8))
				{
					$objReg_a['value'] = $objReg_a['value'] ^ $source_a['value'];
				}
			}
			else if ($objReg_a['bits'] === 16)
			{
				// 16 bit registers

				if (is_int($source_a))
				{
					$objReg_a['value'] = $objReg_a['value'] ^ $source_a;
				}
				else if (($source_a['type'] === 'register') && ($source_a['bits'] === 8))
				{
					$objReg_a['value'] = $objReg_a['value'] ^ $source_a['value'];
				}
			}
			
			if ($objReg_a['value'] === 0)
			{
				$this->set($this->Z, 1);
			}
			else
			{
				$this->set($this->Z, 0);
			}
		}
		else
		{
			$this->invalidMode('xor', 'Can only XOR against a register.');
		}
	}
	
	// block instructions
	
	// TODO: cpd(M(<R16>), <R16>, <value>);
	// TODO: cpd(M(<R16>), <R16>, <R8>);
	//		eg: cpd hl, bc, a
	function cpd()
	{
		// cp (hl)
		// dec hl
		// dec bc
		$this->instructionNotImplemented('cpd');
	}
	
	// TODO: cpdr(M(<R16>), <R16>, <value>);
	// TODO: cpdr(M(<R16>), <R16>, <R8>);
	//		eg: cpdr hl, bc, a
	function cpdr()
	{
		// repeat cpd until either: bc = 0 or a = (hl)
		$this->instructionNotImplemented('cpdr');
	}
	
	// TODO: cpi(M(<R16>), <R16>, <value>);
	// TODO: cpi(M(<R16>), <R16>, <R8>);
	//		eg: cpi hl, bc, a
	function cpi()
	{
		// cp (hl)
		// inc hl
		// dec bc
		$this->instructionNotImplemented('cpi');
	}
	
	// TODO: cpi(M(<R16>), <R16>, <value>);
	// TODO: cpi(M(<R16>), <R16>, <R8>);
	//		eg: cpir hl, bc, a
	function cpir()
	{
		// repeat cpi until either: bc = 0 or a = (hl)
		$this->instructionNotImplemented('cpir');
	}
	
	// TODO: ldd(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldd()
	{
		// ld (de), (hl) then dec de, hl, bc
		$this->instructionNotImplemented('ldd');
	}
	
	// TODO: lddr(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function lddr()
	{
		// repeat ldd until bc = 0, if bc was 0 at the start it loops around until bc = 0 again
		$this->instructionNotImplemented('lddr');
	}
	
	// TODO: ldi(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldi()
	{
		// ld (de), (hl) then inc de, hl and dec bc
		$this->instructionNotImplemented('ldi');
	}
	
	// TODO: ldir(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldir()
	{
		// repeat ldi until bc = 0, if bc was 0 at the start it loops around until bc = 0 again
		$this->instructionNotImplemented('ldir');
	}
	
	// invoke
	$this->run(strInput_a);
	
	// function test()
	// {
		// alert('test');
	// }
	
	// ld(A15, test);
	// call(A15);
	
	//ld(A16, 15);
	//ld(B8, 255);
	//xor(A16, 255);
	//alert(A16.value);
	//ld(B16, 20);
	//ld(L(A16), 255);
	//ld(H(A16), 255);
	//alert(A16.value);
	//ld(M(A16), B16);
	//incm(A16);
	//incm(A16);
	//incm(A16);
	//inc(M(A16));
	//inc(M(A16));
	//inc(M(A16));
	//ld(B16, 0);
	//ld(C16, M(A16));
	//ld(C16, M(0));
	//alert(C16.value);
	
	// ld(L(A16), 0);
	// alert(getLowByte(A16.value));
	// alert(getHighByte(A16.value));
	// alert(A16.value);
	//n = setLowByte(n, 129);
	//n = setHighByte(n, 255);
}
