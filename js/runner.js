// JSASM v0.1 (6 November 2021 - 14 February 2023) By Julian Cassin:
// 
// This is a work in progress JavaScript virtual assembler runner.
//
// https://www.masswerk.at/6502/6502_instruction_set.html & http://www.6502.org/tutorials/vflag.html
// https://atjs.mbnet.fi/mc6809/Information/6809.htm & https://www.maddes.net/m6809pm/sections.htm
// http://map.grauw.nl/resources/z80instr.php
// https://atariage.com/forums/topic/247596-z80-6502-recompiler-used-with-pentagram-port/

// TODO:
//		search for instructionNotImplemented and TODO below

// registers
//
// js				6502						z80						6809
// ==				====						===						====
// A8, A8'			A							A						A
// B8, B8'			X							B						B
// C8, C8'			Y							C						D
// D8, D8'			simulated					simulated				simulated
// E8, E8'			simulated					simulated				simulated

// A16, A16'		simulated					HL						simulated
// B16, B16'		simulated					DE						simulated
// C16, C16'		simulated					IX						X
// D16, D16'		simulated					IY						Y
// E16, E16'		simulated					simulated				simulated

// F, F'			S							F						CC
// PC				PC							PC						PC
// SP				simulated					SP						S or U?

// modifiers
//
// js				6502						z80						6809
// ==				====						===						====
// H				virtual						virtual					virtual
// L				virtual						virtual					virtual
// M				virtual						virtual					virtual

// flags
//
// js				6502						z80						6809
// ==				====						===						====
// C (carry)		c							c						c
// I (interrupt)	i							simulated				simulated
// Z (zero)			z							z						z

// flags (note supported)
//
// js				6502						z80						6809
// ==				====						===						====
// H? (half carry)	d							hc
// N? (negative)	n							
// V? (overflow)	v							p/o

// instruction summary
//
// valid statuses: R = Simulator, 6 = 6502, Z = Z80
//
// status			vasm				6502						z80
// ======			====				====						===

// (general register movement instructions)

// R				alt					simulated					exx
// R				ld					simulated					ld
//										lda,ldx,ldy	
//										sta,stx,sty
//										tax, txa
//										tay, tya
//										tsx, txs
// R				nop					nop							nop
// R				pop					pla,plp						pop
// R				push				pha,php						push
// R				swap				simulated					ex & simulated

// (flow control instructions)

// R				call				jsr							call
// R				if(test(			simulated					simulated
// R				return				ret							ret
// R				while(test(			simulated					simulated

// (arithmetic instructions)

// R				adc					adc							adc
// R				add					?							add
// R				cp					cmp,cpx,cpy					cp
// R				dec					dex,dey						dec
// DEPRECATED		decm				dec							simulated: ld hl,m; dec (hl)
// R				div					simulated					simulated
// R				inc					inx,iny						inc
// DEPRECATED		incm				inc							simulated: ld hl,m; inc (hl)
// R				mod					simulated					simulated
// R				mult				simulated					simulated
// R				sbc					sbc							sbc
// R				sub					?							sub

// (input / output instructions)

//	INPUT			input				simulated					in
//	R				output				simulated					out

// (bit instructions)

// R				and					and							and
// R				or					ora							or
// BITOP			rl					rol							rla
// BITOP			rr					ror							rra
// R				set(C				clc							simulated: and a
// R				set(I				sei							di, ei
// R				set(Z				simulated					simulated
// R				set(bit				simulated					set res
// BITOP			sl					asl							sla
// BITOP			sr					lsr							sra
// BITOP			test(<bit			simulated					bit
// R				xor					eor							xor

// (block instructions)

// MEMOP			cpd					simulated					cpd
// MEMOP			cpdr				simulated					cpdr
// MEMOP			cpi					simulated					cpi
// MEMOP			cpir				simulated					cpir

// MEMOP			ldd					simulated					ldd
// MEMOP			lddr				simulated					lddr
// MEMOP			ldi					simulated					ldi
// MEMOP			ldir				simulated					ldir

// replaced instructions: by while(test(,  if(test(  and return

// 					jp					jmp, clv+bvc/bvs			jp
// 					jpnc				bcc							jp nc
// 					jpc					bcs							jp c
// 					jrnc				bcc							jr nc
// 					jrc					bcs							jr c
// 					jpnz				bne							jp nz
// 					jpz					beq							jp z
// 					jrnz				bne							jr nz
// 					jrz					beq							jr z
// 					reti				rti							reti

function runner(strInput_a, objOptions_a)
{
	var CONSOLEOUTPORT = 1;
	
	// runner options
	var objOptions = {
		//debugassembler: false,
		consoleelement: ''
		//errorelement: '',
		//includecomments: true,
		//includeignoredstatements: false,
		//includesourceascomments: false,
		//includesourcewhitespace: false,
		//showsourcelinenumbers: false
	};
	
	if (objOptions_a !== undefined)
	{
		//if (objOptions_a.debugassembler !== undefined) { objOptions.debugassembler = objOptions_a.debugassembler; }
		if (objOptions_a.consoleelement !== undefined) { objOptions.consoleelement = objOptions_a.consoleelement; }
		//if (objOptions_a.errorelement !== undefined) { objOptions.errorelement = objOptions_a.errorelement; }
		//if (objOptions_a.includecomments !== undefined) { objOptions.includecomments = objOptions_a.includecomments; }
		//if (objOptions_a.includeignoredstatements !== undefined) { objOptions.includeignoredstatements = objOptions_a.includeignoredstatements; }
		//if (objOptions_a.includesourceascomments !== undefined) { objOptions.includesourceascomments = objOptions_a.includesourceascomments; }
		//if (objOptions_a.includesourcewhitespace !== undefined) { objOptions.includesourcewhitespace = objOptions_a.includesourcewhitespace; }
		//if (objOptions_a.showsourcelinenumbers !== undefined) { objOptions.showsourcelinenumbers = objOptions_a.showsourcelinenumbers; }
	}

	// utils
	function doNothing()
	{
		var todo = 'this function is just to stop lint errors where we dont want to do anything';
	}
	
	function todo()
	{
		var todo = 'this function is just to stop lint errors where we things to do';
	}
	
	function instructionNotImplemented(str_a)
	{
		consoleOutput("Instruction '" + str_a + "' not implemented");
	}
	
	function invalidMode(str1_a, str2_a)
	{
		consoleOutput("Invalid mode for instruction '" + str1_a + "'. " + str2_a + ".");
	}
	
	function memoryException(str_a)
	{
		consoleOutput("Memory Exception at address " + str_a + ".");
	}
	
	function memoryInitialised(objMemory_a)
	{
		consoleOutput("Memory '" + objMemory_a.id + "' initialised.");
	}
	
	function consoleOutput(str_a)
	{
		var objDiv = document.getElementById(objOptions.consoleelement);
		objDiv.innerHTML += str_a + '<br />';
	}
	
	function getLowByte(int16BitValue_a)
	{
		return int16BitValue_a & 0xFF;
	}
	
	function getHighByte(int16BitValue_a)
	{
		return int16BitValue_a >> 8;
	}
	
	// process an array util
	function processArray(arr_a, cb_a)
	{
		var blnCancelled = false;
		var intI = 0;
		
		while ((intI < arr_a.length) && !blnCancelled)
		{
			if ($.isFunction(cb_a))
			{
				blnCancelled = cb_a(arr_a[intI], intI);
			}
			
			intI++;
		}
	}

	function setLowByte(objL_a, int8BitValue_a)
	{
		var intHigh = getHighByte(objL_a.source.value);
		var intNewValue = (intHigh << 8) + int8BitValue_a;

		if ((objL_a.type === 'lowbyte') && (objL_a.source.type === 'register'))
		{
			objL_a.value = getLowByte(intNewValue);
			objL_a.source.value = intNewValue;
		}
	}
	
	function setHighByte(objH_a, int8BitValue_a)
	{
		var intLow = getLowByte(objH_a.source.value);
		var intNewValue = (int8BitValue_a << 8) + intLow;
		
		if ((objH_a.type === 'highbyte') && (objH_a.source.type === 'register'))
		{
			objH_a.value = getHighByte(intNewValue);
			objH_a.source.value = intNewValue;
		}
	}
	
	function readMemory8Bits(intAddress_a)
	{
		var intResult = null;
		
		var objMemory = getMemoryByAddressForRead(intAddress_a);
		if (objMemory === null)
		{
			memoryException(intAddress_a);
		}
		else
		{
			//alert('r:' + (intAddress_a - objMemory.startaddress));
			intResult = objMemory[intAddress_a - objMemory.startaddress];
			//alert(intResult);
		}
		
		return intResult;
	}
	
	function readMemory16Bits(intAddress_a)
	{
		var intAddress = intAddress_a;

		var intLow = readMemory8Bits(intAddress);
		intAddress++;
		var intHigh = readMemory8Bits(intAddress);

		return (intHigh << 8) + intLow;
	}
	
	function writeMemory8Bits(intAddress_a, int8BitValue_a)
	{
		var objMemory = getMemoryByAddressForWrite(intAddress_a);
		if (objMemory === null)
		{
			memoryException(intAddress_a);
		}
		else
		{
			//alert('w:' + (intAddress_a - objMemory.startaddress));
			//alert(int8BitValue_a);
			objMemory[intAddress_a - objMemory.startaddress] = int8BitValue_a;
		}
	}
	
	function writeMemory16Bits(intAddress_a, int16BitValue_a)
	{
		var intLow = getLowByte(int16BitValue_a);
		var intHigh = getHighByte(int16BitValue_a);

		var intAddress = intAddress_a;
		writeMemory8Bits(intAddress, intLow);
		intAddress++;
		writeMemory8Bits(intAddress, intHigh);
	}
	
	function getMemoryByAddressForRead(intAddress_a)
	{
		var objResult = null;
		
		// find a non-main address range that our address falls within, include ROM for reads
		processArray(arrMemory, function(objMemory_a)
		{
			if ((objMemory_a.id !== 'main') && (objMemory_a.isenabled))
			{
				if ((objMemory_a.startaddress <= intAddress_a) && (objMemory_a.endaddress >= intAddress_a))
				{
					initialiseMemory(objMemory_a);
					objResult = objMemory_a;
					return true;
				}
			}
		});
		
		if (objResult === null)
		{
			// find a main address range that our address falls within
			processArray(arrMemory, function(objMemory_a)
			{
				if ((objMemory_a.id === 'main') && (objMemory_a.isenabled))
				{
					//alert(objMemory_a.startaddress + ":" + objMemory_a.endaddress + ":" + intAddress_a);
					if ((objMemory_a.startaddress <= intAddress_a) && (intAddress_a <= objMemory_a.endaddress))
					{
						initialiseMemory(objMemory_a);
						objResult = objMemory_a;
						return true;
					}
				}
			});
		}
		
		return objResult;
	}
	
	function getMemoryByAddressForWrite(intAddress_a)
	{
		var objResult = null;
		
		// find a non-main address range that our address falls within, also skip ROM for write
		processArray(arrMemory, function(objMemory_a)
		{
			if ((objMemory_a.id !== 'main') && (objMemory_a.subtype !== 'rom') && (objMemory_a.isenabled))
			{
				if ((objMemory_a.startaddress <= intAddress_a) && (objMemory_a.endaddress >= intAddress_a))
				{
					initialiseMemory(objMemory_a);
					objResult = objMemory_a;
					return true;
				}
			}
		});
		
		if (objResult === null)
		{
			// find a main address range that our address falls within
			processArray(arrMemory, function(objMemory_a)
			{
				if ((objMemory_a.id === 'main') && (objMemory_a.isenabled))
				{
					//alert(objMemory_a.startaddress + ":" + objMemory_a.endaddress + ":" + intAddress_a);
					if ((objMemory_a.startaddress <= intAddress_a) && (intAddress_a <= objMemory_a.endaddress))
					{
						initialiseMemory(objMemory_a);
						objResult = objMemory_a;
						return true;
					}
				}
			});
		}
		
		return objResult;
	}
	
	function getMemoryByPort(intPort_a)
	{
		var objResult = null;
		
		// find a non-main address range that our address falls within
		processArray(arrMemory, function(objMemory_a)
		{
			if ((objMemory_a.enableport === intPort_a) || (objMemory_a.disableport === intPort_a))
			{
				initialiseMemory(objMemory_a);
				objResult = objMemory_a;
				return true;
			}
		});
		
		return objResult;
	}
	
	function initialiseMemory(objMemory_a)
	{
		if (!objMemory_a.isinitialised)
		{
			var intSize = objMemory_a.endaddress - objMemory_a.startaddress;
			objMemory_a.content = new Uint8Array(intSize);
			objMemory_a.isinitialised = true;
			memoryInitialised(objMemory_a);
		}
	}

	// stack & other memory
	var arrMemory = [];
	var arrStack = [];

	// registers
	var A8 = {
		id:'A8',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'accumulator',
		value:0,
		altvalue:0
	};

	var B8 = {
		id:'B8',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var C8 = {
		id:'C8',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var D8 = {
		id:'D8',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var E8 = {
		id:'E8',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var A16 = {
		id:'A16',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'accumulator',
		value:0,
		altvalue:0
	};

	var B16 = {
		id:'B16',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var C16 = {
		id:'C16',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var D16 = {
		id:'D16',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var E16 = {
		id:'F16',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'general',
		value:0,
		altvalue:0
	};

	var F = {
		id:'F',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'flags',
		value:0,
		altvalue:0
	};

	var T = {	// temporary register used by system
		id:'T',
		bits:8,
		canaddress: false,
		type:'register',
		subtype:'flags',
		value:0,
		altvalue:0
	};

	// var M = {
		// id:'M',
		// bits:16,
		// canaddress: true,
		// type:'register',
		// subtype:'memorypointer',
		// value:0,
		// altvalue:0
	// };

	var PC = {
		id:'PC',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'programcounter',
		value:0
	};

	var SP = {
		id:'SP',
		bits:16,
		canaddress: true,
		type:'register',
		subtype:'stackpointer',
		value:0
	};

	// CPU flag states

	var NC = {
		id:'NC',
		type:'flagstate',
		value:'NC'
	};

	var C = {
		id:'C',
		type:'flagstate',
		value:'C'
	};

	var I = {
		id:'I',
		type:'flagstate',
		value:'I'
	};

	var NZ = {
		id:'NZ',
		type:'flagstate',
		value:'NZ'
	};

	var Z = {
		id:'Z',
		type:'flagstate',
		value:'Z'
	};

	// CPU flags

	var flagC = {
		id:'flagC',
		type:'flag',
		value:0
	};

	var flagI = {
		id:'flagI',
		type:'flag',
		value:0
	};

	var flagZ = {
		id:'flagZ',
		type:'flag',
		value:0
	};

	// instructions
	
	// modifiers
	
	function M(source_a)
	{
		var objResult = {
			id:'MA',
			bits:16,
			canaddress: true,
			type:'memoryaddress',
			subtype:'general',
			value:0,
			altvalue:0,
			source:source_a
		};
		
		if (Number.isInteger(source_a))
		{
			objResult.value = source_a;
		}
		else if ((source_a.type === 'register') && (source_a.bits === 16))
		{
			objResult.value = source_a.value;
		}
		else
		{
			invalidMode('M', 'Invalid argument, must be a literal or 16 bit register.');
		}
		
		return objResult;
	}
	
	function H(source_a)
	{
		var objResult = {
			id:'HB',
			bits:8,
			canaddress: true,
			type:'highbyte',
			subtype:'general',
			value:0,
			altvalue:0,
			source:source_a
		};
		
		if (Number.isInteger(source_a))
		{
			objResult.value = getHighByte(source_a);
		}
		else if ((source_a.type === 'register') && (source_a.bits === 16))
		{
			objResult.value = getHighByte(source_a.value);
		}
		else
		{
			invalidMode('H', 'Invalid Argument, must be a literal or 16 bit register.');
		}
		
		return objResult;
	}
	
	function L(source_a)
	{
		var objResult = {
			id:'LB',
			bits:8,
			canaddress: true,
			type:'lowbyte',
			subtype:'general',
			value:0,
			altvalue:0,
			source:source_a
		};
		
		if (Number.isInteger(source_a))
		{
			objResult.value = getLowByte(source_a);
		}
		else if ((source_a.type === 'register') && (source_a.bits === 16))
		{
			objResult.value = getLowByte(source_a.value);
		}
		else
		{
			invalidMode('L', 'Invalid argument, must be a literal or 16 bit register.');
		}

		return objResult;
	}
	
	// general register movement instructions

	// alt();
	function alt()
	{
		var temp;
		
		temp = A8.value; A8.value = A8.altvalue; A8.altvalue = temp;
		temp = B8.value; B8.value = B8.altvalue; B8.altvalue = temp;
		temp = C8.value; C8.value = C8.altvalue; C8.altvalue = temp;
		temp = D8.value; D8.value = D8.altvalue; D8.altvalue = temp;
		temp = E8.value; E8.value = E8.altvalue; E8.altvalue = temp;
		
		temp = A16.value; A16.value = A16.altvalue; A16.altvalue = temp;
		temp = B16.value; B16.value = B16.altvalue; B16.altvalue = temp;
		temp = C16.value; C16.value = C16.altvalue; C16.altvalue = temp;
		temp = D16.value; D16.value = D16.altvalue; D16.altvalue = temp;
		temp = E16.value; E16.value = E16.altvalue; E16.altvalue = temp;

		temp = F.value; F.value = F.altvalue; F.altvalue = temp;
		//temp = M.value; M.value = M.altvalue; M.altvalue = temp;
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
	function ld(p1_a, p2_a)
	{
		if (p1_a.type === 'register')
		{
			// ASSIGN TO 8 BIT REGISTERS, SUCH AS A8 =
			if (p1_a.bits === 8)
			{
				// 8 bit registers

				if (Number.isInteger(p2_a))
				{
					// A <value>
//alert('a');
					p1_a.value = getLowByte(p2_a);
				}
				else if ((p2_a.type === 'register') && (p2_a.bits === 8))
				{
					// B <R8>
alert('b');
					p1_a.value = p2_a.value;
				}
				else if ((p2_a.type === 'memoryaddress') && (Number.isInteger(p2_a.source)))
				{
					// C M(<value>)
//alert('c');
					p1_a.value = readMemory8Bits(p2_a.source);
				}
				else if ((p2_a.type === 'memoryaddress') && (p2_a.source.type === 'register'))
				{
					// D M(<R16>)
alert('d');
					p1_a.value = readMemory8Bits(p2_a.source.value);
				}
				else if (p2_a.type === 'lowbyte')
				{
					// E L(<R16>)
alert('e');
					p1_a.value = p2_a.value;
				}
				else if (p2_a.type === 'highbyte')
				{
					// F H(<R16>)
alert('f');
					p1_a.value = p2_a.value;
				}
			}
			// ASSIGN TO 16 BIT REGISTERS, SUCH AS A16 =
			else if (p1_a.bits === 16)
			{
				// 16 bit registers
				
				if (Number.isInteger(p2_a))
				{
					// G <value>
//alert('g');
					p1_a.value = p2_a;
				}
				else if ($.isFunction(p2_a))
				{
					// H <function>
alert('h');
					p1_a.value = p2_a;
				}
				else if ((p2_a.type === 'register') && (p2_a.bits === 16))
				{
					// I <R16>
alert('i');
					p1_a.value = p2_a.value;
				}
				else if ((p2_a.type === 'memoryaddress') && (Number.isInteger(p2_a.source)))
				{
					// J M(<value>)
//alert('j');
					p1_a.value = readMemory16Bits(p2_a.source);
				}
				else if ((p2_a.type === 'memoryaddress') && (p2_a.source.type === 'register'))
				{
					// K M(<R16>)
//alert('k');
					p1_a.value = readMemory16Bits(p2_a.source.value);
				}
			}
		}
		else if (p1_a.type === 'memoryaddress')
		{
			// all 16 bits
			// ASSIGN TO LITERAL MEMORY ADDRESS, SUCH AS M(0) =
			if (Number.isInteger(p1_a.source))
			{
				if ((p2_a.type === 'register') && (p2_a.bits === 8))
				{
					// L <R8>
//alert('l:' + p1_a.source + ":" + p2_a.value);
					writeMemory8Bits(p1_a.source, p2_a.value);
				}
				else if ((p2_a.type === 'register') && (p2_a.bits === 16))
				{
					// M <R16>
//alert('m');
					writeMemory16Bits(p1_a.source, p2_a.value);
				}
				else if (p2_a.type === 'lowbyte')
				{
					// N L(<R16>)
alert('n');
					writeMemory8Bits(p1_a.source, p2_a.value);
				}
				else if (p2_a.type === 'highbyte')
				{
					// O H(<R16>)
alert('o');
					writeMemory8Bits(p1_a.source, p2_a.value);
				}
			}
			// ASSIGN TO MEMORY ADDRESS POINTED TO BY REGISTER, SUCH AS M(A16) =
			else if ((p1_a.source.type === 'register') && (p1_a.source.bits === 16))
			{
				if ((p2_a.type === 'register') && (p2_a.bits === 8))
				{
					// P <R8>
alert('p');
					writeMemory8Bits(p1_a.source.value, p2_a.value);
				}
				else if ((p2_a.type === 'register') && (p2_a.bits === 16))
				{
					// Q <R16>
//alert('q');
					writeMemory16Bits(p1_a.source.value, p2_a.value);
				}
				else if (p2_a.type === 'lowbyte')
				{
					// R L(<R16>)
alert('r');
					writeMemory16Bits(p1_a.source.value, p2_a.value);
				}
				else if (p2_a.type === 'highbyte')
				{
					// S H(<R16>)
alert('s');
					writeMemory16Bits(p1_a.source.value, p2_a.value);
				}
			}
			else
			{
				invalidMode('ld', 'Invalid load into memory address.');
			}
		}
		// ASSIGN TO HIGH BYTES, SUCH AS H(A16) =
		else if (p1_a.type === 'highbyte')
		{
			// all 8 bits
			if (Number.isInteger(p2_a))
			{
				// T <value>
//alert('t');
				setHighByte(p1_a, p2_a);
			}
			else if ((p2_a.type === 'register') && (p2_a.bits === 8))
			{
				// U <R8>
alert('u');
				setHighByte(p1_a, p2_a.value);
			}
			else if ((p2_a.type === 'memoryaddress') && (Number.isInteger(p2_a.source)))
			{
				// V M(<value>)
alert('v');
				setHighByte(p1_a, readMemory8Bits(p2_a.source));
			}
			else if ((p2_a.type === 'memoryaddress') && (p2_a.source.type === 'register'))
			{
				// W M(<R16>)
alert('w');
				setHighByte(p1_a, readMemory8Bits(p2_a.source.value));
			}
		}
		// ASSIGN TO LOW BYTES, SUCH AS L(A16) =
		else if (p1_a.type === 'lowbyte')
		{
			// all 8 bits
			if (Number.isInteger(p2_a))
			{
				// X <value>
//alert('x');
				setLowByte(p1_a, p2_a);
			}
			else if ((p2_a.type === 'register') && (p2_a.bits === 8))
			{
				// Y <R8>
alert('y');
				setLowByte(p1_a, p2_a.value);
			}
			else if ((p2_a.type === 'memoryaddress') && (Number.isInteger(p2_a.source)))
			{
				// Z M(<value>)
alert('z');
				setLowByte(p1_a, readMemory8Bits(p2_a.source));
			}
			else if ((p2_a.type === 'memoryaddress') && (p2_a.source.type === 'register'))
			{
				// 0 M(<R16>)
alert('0');
				setLowByte(p1_a, readMemory8Bits(p2_a.source.value));
			}
		}
		else
		{
			invalidMode('ld', 'Invalid load into memory address.');
		}
	}

	// nop();
	function nop() { }
	
	// pop(<R8>);
	// pop(<R16>);
	function pop(objReg_a)
	{
		objReg_a.value = arrStack.pop();
	}

	// push(<R8>);
	// push(<R16>);
	function push(objReg_a)
	{
		arrStack.push(objReg_a.value);
	}

	// swap(<R8>, <R8>);
	// swap(<R16>, <R16>);
	function swap(objReg1_a, objReg2_a)
	{
		var temp;
		
		if (objReg1_a.bits === objReg2_a.bits)
		{
			temp = objReg1_a.value; objReg1_a.value = objReg2_a.altvalue; objReg2_a.altvalue = temp;
		}
		else
		{
			invalidMode('swap', 'Cannot swap registers of different sizes.');
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
	function call(objFlagState_a, cb_a)
	{
		var cb = cb_a;
		
		if (cb === undefined)
		{
			// unconditional call
			cb = objFlagState_a;

			if ($.isFunction(cb))
			{
				cb();
			}
			else if ((cb.type === 'register') && (cb.canaddress) && ($.isFunction(cb.value)))
			{
				cb.value();
			}
		}
		else
		{
			if ((cb.type === 'register') && (cb.canaddress) && ($.isFunction(cb.value)))
			{
				// conditional call
				if ((objFlagState_a.id === 'NC') && (flagC.value === 0))
				{
					cb.value();
				}
				else if ((objFlagState_a.id === 'C') && (flagC.value === 1))
				{
					cb.value();
				}
				else if ((objFlagState_a.id === 'NZ') && (flagZ.value === 0))
				{
					cb.value();
				}
				else if ((objFlagState_a.id === 'Z') && (flagZ.value === 1))
				{
					cb.value();
				}
			}
			else
			{
				// conditional call
				if ((objFlagState_a.id === 'NC') && (flagC.value === 0))
				{
					cb();
				}
				else if ((objFlagState_a.id === 'C') && (flagC.value === 1))
				{
					cb();
				}
				else if ((objFlagState_a.id === 'NZ') && (flagZ.value === 0))
				{
					cb();
				}
				else if ((objFlagState_a.id === 'Z') && (flagZ.value === 1))
				{
					cb();
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
	function test(objFlagState_a)
	{
		var blnResult = false;

		if ((objFlagState_a.id === 'NC') && (flagC.value === 0))
		{
			blnResult = true;
		}
		else if ((objFlagState_a.id === 'C') && (flagC.value === 1))
		{
			blnResult = true;
		}
		else if ((objFlagState_a.id === 'NZ') && (flagZ.value === 0))
		{
			blnResult = true;
		}
		else if ((objFlagState_a.id === 'Z') && (flagZ.value === 1))
		{
			blnResult = true;
		}

		return blnResult;
	}

	// arithmetic instructions
	
	// adc(<R8>, <value>);
	// adc(<R8>, <R8>);
	// adc(<R16>, <value>);
	// adc(<R16>, <R16>);
	// TODO: adc(M(<R16>), <value>);
	// TODO: adc(<R16>, M(<R16>));
	// TODO: adc(M(<R16>), <R16>);
	function adc(objReg_a, source_a)
	{
		var blnCarry = test(C);
		
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = objReg_a.value + source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = objReg_a.value + source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('adc', 'Cannot add a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('adc', 'Cannot add a function call.');
			}
			else
			{
				invalidMode('adc', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('adc', 'You can only add a value or a register.');
		}
		
		if (blnCarry)
		{
			objReg_a.value++;
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value > 255)
			{
				objReg_a.value = objReg_a.value % 256;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value > 65535)
			{
				objReg_a.value = objReg_a.value % 65536;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}
	
	// add(<R8>, <value>);
	// add(<R8>, <R8>);
	// add(<R16>, <value>);
	// add(<R16>, <R16>);
	// TODO: add(M(<R16>), <value>);
	// TODO: add(<R16>, M(<R16>));
	// TODO: add(M(<R16>), <R16>);
	function add(objReg_a, source_a)
	{
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = objReg_a.value + source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = objReg_a.value + source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('add', 'Cannot add a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('add', 'Cannot add a function call.');
			}
			else
			{
				invalidMode('add', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('add', 'You can only add a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value > 255)
			{
				objReg_a.value = objReg_a.value % 256;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value > 65535)
			{
				objReg_a.value = objReg_a.value % 65536;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}
	
	// cp(<R8>, <value>);
	// cp(<R8>, <R8>);
	// cp(<R16>, <value>);
	// cp(<R16>, <R16>);
	// TODO: cp(M(<R16>), <value>);
	// TODO: cp(<R16>, M(<R16>));
	// TODO: cp(M(<R16>), <R16>);
	function cp(objReg_a, source_a)
	{
		var intValue = objReg_a.value;
		
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			intValue = intValue - source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				intValue = intValue - source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('cp', 'Cannot compare a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('cp', 'Cannot compare a function call.');
			}
			else
			{
				invalidMode('cp', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('cp', 'You can only compare a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (intValue < 0)
			{
				intValue = 256 + intValue;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (intValue < 0)
			{
				intValue = 65536 + intValue;
				set(C, 1);
			}
		}


		if (intValue === 0)
		{
			set(Z, 1);
		}
	}

	// dec(R8);
	// dec(R16);
	// dec(M(<R16>));
	function dec(objReg_a)
	{
		if ((objReg_a.type === 'memoryaddress') && (objReg_a.source.type === 'register'))
		{
			ld(T8, objReg_a);
			dec(T8);
			ld(objReg_a, T8);
		}
		else
		{
			objReg_a.value--;
			flagZ.value = 0;

			if (objReg_a.value === -1)
			{
				if (objReg_a.bits === 8)
				{
					objReg_a.value = 255;
				}
				else if (objReg_a.bits === 16)
				{
					objReg_a.value = 65535;
				}
			}

			if (objReg_a.value === 0)
			{
				flagZ.value = 1;
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
	function div(objReg_a, source_a)
	{
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = parseInt(objReg_a.value / source_a, 10);
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = parseInt(objReg_a.value / source_a.value, 10);
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('div', 'Cannot divide a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('div', 'Cannot divide a function call.');
			}
			else
			{
				invalidMode('div', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('div', 'You can only divide a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 256 + objReg_a.value;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 65536 + objReg_a.value;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}
	
	// inc(R8);
	// inc(R16);
	// inc(M(<R16>));
	function inc(objReg_a)
	{
		if ((objReg_a.type === 'memoryaddress') && (objReg_a.source.type === 'register'))
		{
			ld(T8, objReg_a);
			inc(T8);
			ld(objReg_a, T8);
		}
		else
		{
			objReg_a.value++;
			flagZ.value = 0;

			if (((objReg_a.bits === 8) && (objReg_a.value === 256)) || ((objReg_a.bits === 16) && (objReg_a.value === 65536)))
			{
				objReg_a.value = 0;
				flagZ.value = 1;
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
	function sbc(objReg_a, source_a)
	{
		var blnCarry = test(C);
		
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = objReg_a.value - source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = objReg_a.value - source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('sbc', 'Cannot subtract a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('sbc', 'Cannot subtract a function call.');
			}
			else
			{
				invalidMode('sbc', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('sbc', 'You can only subtract a value or a register.');
		}
		
		if (blnCarry)
		{
			objReg_a.value--;
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 256 + objReg_a.value;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 65536 + objReg_a.value;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}

	// mod(<R8>, <value>);
	// mod(<R8>, <R8>);
	// mod(<R16>, <value>);
	// mod(<R16>, <R16>);
	// TODO: mod(M(<R16>), <value>);
	// TODO: mod(<R16>, M(<R16>));
	// TODO: mod(M(<R16>), <R16>);
	function mod(objReg_a, source_a)
	{
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = parseInt(objReg_a.value % source_a, 10);
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = parseInt(objReg_a.value % source_a.value, 10);
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('mod', 'Cannot calculate remainder of a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('mod', 'Cannot calculate remainder of a function call.');
			}
			else
			{
				invalidMode('mod', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('mod', 'You can only calculate remainder of a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 256 + objReg_a.value;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 65536 + objReg_a.value;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}
	
	// mult(<R8>, <value>);
	// mult(<R8>, <R8>);
	// mult(<R16>, <value>);
	// mult(<R16>, <R16>);
	// TODO: mult(M(<R16>), <value>);
	// TODO: mult(<R16>, M(<R16>));
	// TODO: mult(M(<R16>), <R16>);
	function mult(objReg_a, source_a)
	{
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = objReg_a.value * source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = objReg_a.value * source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('mult', 'Cannot multiply a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('mult', 'Cannot multiply a function call.');
			}
			else
			{
				invalidMode('mult', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('mult', 'You can only multiply a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value > 255)
			{
				objReg_a.value = objReg_a.value % 256;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value > 65535)
			{
				objReg_a.value = objReg_a.value % 65536;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}
	
	// sub(<R8>, <value>);
	// sub(<R8>, <R8>);
	// sub(<R16>, <value>);
	// sub(<R16>, <R16>);
	// TODO: sub(M(<R16>), <value>);
	// TODO: sub(<R16>, M(<R16>));
	// TODO: sub(M(<R16>), <R16>);
	function sub(objReg_a, source_a)
	{
		set(Z, 0);
		set(C, 0);
		
		if (Number.isInteger(source_a))
		{
			objReg_a.value = objReg_a.value - source_a;
		}
		else if (source_a.type === 'register')
		{
			if (source_a.bits == objReg_a.bits)
			{
				objReg_a.value = objReg_a.value - source_a.value;
			}
			else if ($.isFunction(objReg_a.value))
			{
				invalidMode('sub', 'Cannot subtract a value to a function call.');
			}
			else if ($.isFunction(source_a.value))
			{
				invalidMode('sub', 'Cannot subtract a function call.');
			}
			else
			{
				invalidMode('sub', 'More information is unavailable.');
			}
		}
		else
		{
			invalidMode('sub', 'You can only subtract a value or a register.');
		}

		if (objReg_a.bits === 8)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 256 + objReg_a.value;
				set(C, 1);
			}
		}
		else if (objReg_a.bits === 16)
		{
			if (objReg_a.value < 0)
			{
				objReg_a.value = 65536 + objReg_a.value;
				set(C, 1);
			}
		}


		if (objReg_a.value === 0)
		{
			set(Z, 1);
		}
	}

	// input / output instructions
	
	function input()
	{
		instructionNotImplemented('input');
	}
	
	// output(<value>, <value>);
	// output(<R8>, <value>);
	// output(<R8>, <R8>);
	// output(<R16>, <value>);
	// output(<R16>, <R8>);
	function output(port_a, source_a)
	{
		var intPort = 0;
		
		if (Number.isInteger(port_a))
		{
			intPort = port_a;
		}
		else if (port_a.type === 'register')
		{
			if (Number.isInteger(port_a.value))
			{
				intPort = port_a.value;
			}
			else
			{
				invalidMode('output', 'The port must be a number or a register.');
			}
		}
		
		if (intPort === CONSOLEOUTPORT)
		{
			if (Number.isInteger(source_a))
			{
				consoleOutput(source_a);
			}
			else if (source_a.type === 'register')
			{
				if (source_a.bits === 8)
				{
					if (Number.isInteger(source_a.value))
					{
						consoleOutput(source_a.value);
					}
					else
					{
						invalidMode('output', 'Can only output a value or a register.');
					}
				}
				else
				{
					invalidMode('output', 'Can only output 8 bits at a time.');
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
	function and(objReg_a, source_a)
	{
		if (objReg_a.type === 'register')
		{
			if (objReg_a.bits === 8)
			{
				// 8 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value & getLowByte(source_a);
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value & source_a.value;
				}
			}
			else if (objReg_a.bits === 16)
			{
				// 16 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value & source_a;
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value & source_a.value;
				}
			}
			
			if (objReg_a.value === 0)
			{
				set(Z, 1);
			}
			else
			{
				set(Z, 0);
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
	function or(objReg_a, source_a)
	{
		if (objReg_a.type === 'register')
		{
			if (objReg_a.bits === 8)
			{
				// 8 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value | getLowByte(source_a);
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value | source_a.value;
				}
			}
			else if (objReg_a.bits === 16)
			{
				// 16 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value | source_a;
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value | source_a.value;
				}
			}
			
			if (objReg_a.value === 0)
			{
				set(Z, 1);
			}
			else
			{
				set(Z, 0);
			}
		}
		else
		{
			invalidMode('or', 'Can only OR against a register.');
		}
	}
	
	// TODO: rl(<R8>)
	// TODO: rl(<R16>)
	// TODO: rl(M(<R16>))
	function rl()
	{
		instructionNotImplemented('rl');
	}
	
	// TODO: rr(<R8>)
	// TODO: rr(<R16>)
	// TODO: rr(M(<R16>))
	function rr()
	{
		instructionNotImplemented('rr');
	}
	
	// set(C, <value>);
	// set(I, <value>);
	// set(Z, <value>);
	// TODO: set(<bit>, <R8>, <value>)
	// TODO: set(<bit>, M(<R16>), <value>)
	function set(objFlagState_a, intValue_a)
	{
		if (objFlagState_a.id === 'C')
		{
			flagC.value = intValue_a;
		}
		else if (objFlagState_a.id === 'I')
		{
			flagI.value = intValue_a;
		}
		else if (objFlagState_a.id === 'Z')
		{
			flagZ.value = intValue_a;
		}
	}

	// TODO: sl(<R8>)
	// TODO: sl(<R16>)
	// TODO: sl(M(<R16>))
	function sl()
	{
		instructionNotImplemented('sl');
	}
	
	// TODO: sr(<R8>)
	// TODO: sr(<R16>)
	// TODO: sr(M(<R16>))
	function sr()
	{
		instructionNotImplemented('sr');
	}
	
	// xor(<R8>, <value>);
	// xor(<R8>, <R8>);
	// xor(<R16>, <value>);
	// xor(<R16>, <R16>);
	// TODO: xor(M(<R16>), <value>);
	// TODO: xor(<R16>, M(<R16>));
	// TODO: xor(M(<R16>), <R16>);
	function xor(objReg_a, source_a)
	{
		if (objReg_a.type === 'register')
		{
			if (objReg_a.bits === 8)
			{
				// 8 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value ^ getLowByte(source_a);
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value ^ source_a.value;
				}
			}
			else if (objReg_a.bits === 16)
			{
				// 16 bit registers

				if (Number.isInteger(source_a))
				{
					objReg_a.value = objReg_a.value ^ source_a;
				}
				else if ((source_a.type === 'register') && (source_a.bits === 8))
				{
					objReg_a.value = objReg_a.value ^ source_a.value;
				}
			}
			
			if (objReg_a.value === 0)
			{
				set(Z, 1);
			}
			else
			{
				set(Z, 0);
			}
		}
		else
		{
			invalidMode('or', 'Can only XOR against a register.');
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
		instructionNotImplemented('cpd');
	}
	
	// TODO: cpdr(M(<R16>), <R16>, <value>);
	// TODO: cpdr(M(<R16>), <R16>, <R8>);
	//		eg: cpdr hl, bc, a
	function cpdr()
	{
		// repeat cpd until either: bc = 0 or a = (hl)
		instructionNotImplemented('cpdr');
	}
	
	// TODO: cpi(M(<R16>), <R16>, <value>);
	// TODO: cpi(M(<R16>), <R16>, <R8>);
	//		eg: cpi hl, bc, a
	function cpi()
	{
		// cp (hl)
		// inc hl
		// dec bc
		instructionNotImplemented('cpi');
	}
	
	// TODO: cpi(M(<R16>), <R16>, <value>);
	// TODO: cpi(M(<R16>), <R16>, <R8>);
	//		eg: cpir hl, bc, a
	function cpir()
	{
		// repeat cpi until either: bc = 0 or a = (hl)
		instructionNotImplemented('cpir');
	}
	
	// TODO: ldd(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldd()
	{
		// ld (de), (hl) then dec de, hl, bc
		instructionNotImplemented('ldd');
	}
	
	// TODO: lddr(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function lddr()
	{
		// repeat ldd until bc = 0, if bc was 0 at the start it loops around until bc = 0 again
		instructionNotImplemented('lddr');
	}
	
	// TODO: ldi(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldi()
	{
		// ld (de), (hl) then inc de, hl and dec bc
		instructionNotImplemented('ldi');
	}
	
	// TODO: ldir(M(<R16>), M(<R16>), <R16>);
	//		eg: ldd de, hl, bc
	function ldir()
	{
		// repeat ldi until bc = 0, if bc was 0 at the start it loops around until bc = 0 again
		instructionNotImplemented('ldir');
	}
	
	// initialise memory
	arrMemory.push({
		id:'main',
		isenabled:true,
		isinitialised:false,
		enableport:1000,
		disableport:1001,
		startaddress:0,
		endaddress:65535,
		type:'memory',
		subtype:'ram',
		content:''
	});
	
	arrMemory.push({
		id:'lowerrom',
		isenabled:false,
		isinitialised:false,
		enableport:2000,
		disableport:2001,
		startaddress:0,
		endaddress:16384,
		type:'memory',
		subtype:'rom',
		content:''
	});
	
	arrMemory.push({
		id:'upperrom',
		isenabled:false,
		isinitialised:false,
		enableport:2002,
		disableport:2003,
		startaddress:49152,
		endaddress:65535,
		type:'memory',
		subtype:'rom',
		content:''
	});
	
	arrMemory.push({
		id:'bank5',
		isenabled:false,
		isinitialised:false,
		enableport:3000,
		disableport:3001,
		startaddress:16384,
		endaddress:32768,
		type:'memory',
		subtype:'ram',
		content:''
	});
	
	arrMemory.push({
		id:'bank6',
		isenabled:false,
		isinitialised:false,
		enableport:3002,
		disableport:3003,
		startaddress:16384,
		endaddress:32768,
		type:'memory',
		subtype:'ram',
		content:''
	});
	
	arrMemory.push({
		id:'bank7',
		isenabled:false,
		isinitialised:false,
		enableport:3004,
		disableport:3005,
		startaddress:16384,
		endaddress:32768,
		type:'memory',
		subtype:'ram',
		content:''
	});
	
	arrMemory.push({
		id:'bank8',
		isenabled:false,
		isinitialised:false,
		enableport:3006,
		disableport:3007,
		startaddress:16384,
		endaddress:32768,
		type:'memory',
		subtype:'ram',
		content:''
	});

	// invoke
	eval(strInput_a);
	
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
