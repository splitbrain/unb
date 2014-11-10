function unicodeAlternatives(keycode)
{
	switch (keycode)
	{
	case 0x21: // !
		return("!¡");
	case 0x24: // $
		return("$¢£¥₤€");
	case 0x25: // %
		return("%‰");
	case 0x28: // (
		return("(〈");
	case 0x29: // )
		return(")〉");
	case 0x2a: // *
		return("*∙×∗⊗");
	case 0x2b: // +
		return("+±⁺₊⊕⊢⊥");
	case 0x2d: // -
		return("-¬±←→↔⁻₋⊖⊢⊥");
	case 0x2e: // .
		return(".⋮⋯⋱");

	case 0x30: // 0
		return("0⁰₀°Ø∘");
	case 0x31: // 1
		return("1¹₁½⅓¼⅛");
	case 0x32: // 2
		return("2²₂⅔");
	case 0x33: // 3
		return("3³₃¾");
	case 0x34: // 4
		return("4⁴₄");
	case 0x35: // 5
		return("5⁵₅");
	case 0x36: // 6
		return("6⁶₆");
	case 0x37: // 7
		return("7⁷₇");
	case 0x38: // 8
		return("8⁸₈∞");
	case 0x39: // 9
		return("9⁹₉");

	case 0x3c: // <
		return("<«‹≤≪⊂⊄⊆⊈");
	case 0x3d: // =
		return("=≈≠≡≙≢⇒⇏⇐");
	case 0x3e: // >
		return(">»›≥≫⊃⊅⊇⊉");
	case 0x3f: // ?
		return("?¿�");

	case 0x41: // A
		return("AÀÁÂÄÅÆ∀∡∧");
	case 0x43: // C
		return("CÇĆĈČ⌘©");
	case 0x44: // D
		return("DÐΔ∆⌫");
	case 0x45: // E
		return("EÈÉÊËĚẼ€∃∄∈∉∋∌");
	case 0x49: // I
		return("IÌÍÎÏĨ∣∤∥∦");
	case 0x4c: // L
		return("LĹŁΛ");
	case 0x4e: // N
		return("NÑŃŇ№");
	case 0x4f: // O
		return("OÒÓÔÖØŒΩ∅⌥");
	case 0x50: // P
		return("P∏¶");
	case 0x52: // r
		return("R®ŔŘ");
	case 0x53: // S
		return("SŚŜŠ∑∫∬");
	case 0x54: // T
		return("T™†");
	case 0x55: // U
		return("UÙÚÛÜŨ");
	case 0x59: // Y
		return("YỲÝŸ");
	case 0x5a: // Z
		return("ZŹŽ");

	case 0x61: // a
		return("aàáâäåæǎα∡∧");
	case 0x62: // b
		return("bβ");
	case 0x63: // c
		return("cçćĉčγ©¢⌘");
	case 0x64: // d
		return("dδ⌫");
	case 0x65: // e
		return("eèéêëěεẽ℮€∈∉∋∌");
	case 0x66: // f
		return("fƒ");
	case 0x67: // g
		return("gĝ");
	case 0x68: // h
		return("hĥ");
	case 0x69: // i
		return("iìíîïĩ");
	case 0x6c: // l
		return("l£łλℓ₤∣∤∥∦");
	case 0x6d: // m
		return("mµμ");
	case 0x6e: // n
		return("nñňŋ∩ⁿ");
	case 0x6f: // o
		return("oòóôöøœω○∘⌥");
	case 0x70: // p
		return("pπ");
	case 0x72: // r
		return("r®ŕřρ");
	case 0x73: // s
		return("sßśŝšσ");
	case 0x74: // t
		return("t™†");
	case 0x75: // u
		return("uùúûüµũ∪");
	case 0x76: // v
		return("v√∛∜∨");
	case 0x78: // x
		return("x×⊗");
	case 0x79: // y
		return("yýÿ¥");
	case 0x7a: // z
		return("zźž");

	default:
		return("");
	}
}
