<?php
namespace Catpow;

class Colors{
	const NAMED_COLORS=[
		"black"=>"#000000",
		"silver"=>"#c0c0c0",
		"gray"=>"#808080",
		"white"=>"#ffffff",
		"maroon"=>"#800000",
		"red"=>"#ff0000",
		"purple"=>"#800080",
		"fuchsia"=>"#ff00ff",
		"green"=>"#008000",
		"lime"=>"#00ff00",
		"olive"=>"#808000",
		"yellow"=>"#ffff00",
		"navy"=>"#000080",
		"blue"=>"#0000ff",
		"teal"=>"#008080",
		"aqua"=>"#00ffff",
		"orange"=>"#ffa500",
		"aliceblue"=>"#f0f8ff",
		"antiquewhite"=>"#faebd7",
		"aquamarine"=>"#7fffd4",
		"azure"=>"#f0ffff",
		"beige"=>"#f5f5dc",
		"bisque"=>"#ffe4c4",
		"blanchedalmond"=>"#ffebcd",
		"blueviolet"=>"#8a2be2",
		"brown"=>"#a52a2a",
		"burlywood"=>"#deb887",
		"cadetblue"=>"#5f9ea0",
		"chartreuse"=>"#7fff00",
		"chocolate"=>"#d2691e",
		"coral"=>"#ff7f50",
		"cornflowerblue"=>"#6495ed",
		"cornsilk"=>"#fff8dc",
		"crimson"=>"#dc143c",
		"cyan"=>"#00ffff",
		"darkblue"=>"#00008b",
		"darkcyan"=>"#008b8b",
		"darkgoldenrod"=>"#b8860b",
		"darkgray"=>"#a9a9a9",
		"darkgreen"=>"#006400",
		"darkgrey"=>"#a9a9a9",
		"darkkhaki"=>"#bdb76b",
		"darkmagenta"=>"#8b008b",
		"darkolivegreen"=>"#556b2f",
		"darkorange"=>"#ff8c00",
		"darkorchid"=>"#9932cc",
		"darkred"=>"#8b0000",
		"darksalmon"=>"#e9967a",
		"darkseagreen"=>"#8fbc8f",
		"darkslateblue"=>"#483d8b",
		"darkslategray"=>"#2f4f4f",
		"darkslategrey"=>"#2f4f4f",
		"darkturquoise"=>"#00ced1",
		"darkviolet"=>"#9400d3",
		"deeppink"=>"#ff1493",
		"deepskyblue"=>"#00bfff",
		"dimgray"=>"#696969",
		"dimgrey"=>"#696969",
		"dodgerblue"=>"#1e90ff",
		"firebrick"=>"#b22222",
		"floralwhite"=>"#fffaf0",
		"forestgreen"=>"#228b22",
		"gainsboro"=>"#dcdcdc",
		"ghostwhite"=>"#f8f8ff",
		"gold"=>"#ffd700",
		"goldenrod"=>"#daa520",
		"greenyellow"=>"#adff2f",
		"grey"=>"#808080",
		"honeydew"=>"#f0fff0",
		"hotpink"=>"#ff69b4",
		"indianred"=>"#cd5c5c",
		"indigo"=>"#4b0082",
		"ivory"=>"#fffff0",
		"khaki"=>"#f0e68c",
		"lavender"=>"#e6e6fa",
		"lavenderblush"=>"#fff0f5",
		"lawngreen"=>"#7cfc00",
		"lemonchiffon"=>"#fffacd",
		"lightblue"=>"#add8e6",
		"lightcoral"=>"#f08080",
		"lightcyan"=>"#e0ffff",
		"lightgoldenrodyellow"=>"#fafad2",
		"lightgray"=>"#d3d3d3",
		"lightgreen"=>"#90ee90",
		"lightgrey"=>"#d3d3d3",
		"lightpink"=>"#ffb6c1",
		"lightsalmon"=>"#ffa07a",
		"lightseagreen"=>"#20b2aa",
		"lightskyblue"=>"#87cefa",
		"lightslategray"=>"#778899",
		"lightslategrey"=>"#778899",
		"lightsteelblue"=>"#b0c4de",
		"lightyellow"=>"#ffffe0",
		"limegreen"=>"#32cd32",
		"linen"=>"#faf0e6",
		"magenta"=>"#ff00ff",
		"mediumaquamarine"=>"#66cdaa",
		"mediumblue"=>"#0000cd",
		"mediumorchid"=>"#ba55d3",
		"mediumpurple"=>"#9370db",
		"mediumseagreen"=>"#3cb371",
		"mediumslateblue"=>"#7b68ee",
		"mediumspringgreen"=>"#00fa9a",
		"mediumturquoise"=>"#48d1cc",
		"mediumvioletred"=>"#c71585",
		"midnightblue"=>"#191970",
		"mintcream"=>"#f5fffa",
		"mistyrose"=>"#ffe4e1",
		"moccasin"=>"#ffe4b5",
		"navajowhite"=>"#ffdead",
		"oldlace"=>"#fdf5e6",
		"olivedrab"=>"#6b8e23",
		"orangered"=>"#ff4500",
		"orchid"=>"#da70d6",
		"palegoldenrod"=>"#eee8aa",
		"palegreen"=>"#98fb98",
		"paleturquoise"=>"#afeeee",
		"palevioletred"=>"#db7093",
		"papayawhip"=>"#ffefd5",
		"peachpuff"=>"#ffdab9",
		"peru"=>"#cd853f",
		"pink"=>"#ffc0cb",
		"plum"=>"#dda0dd",
		"powderblue"=>"#b0e0e6",
		"rosybrown"=>"#bc8f8f",
		"royalblue"=>"#4169e1",
		"saddlebrown"=>"#8b4513",
		"salmon"=>"#fa8072",
		"sandybrown"=>"#f4a460",
		"seagreen"=>"#2e8b57",
		"seashell"=>"#fff5ee",
		"sienna"=>"#a0522d",
		"skyblue"=>"#87ceeb",
		"slateblue"=>"#6a5acd",
		"slategray"=>"#708090",
		"slategrey"=>"#708090",
		"snow"=>"#fffafa",
		"springgreen"=>"#00ff7f",
		"steelblue"=>"#4682b4",
		"tan"=>"#d2b48c",
		"thistle"=>"#d8bfd8",
		"tomato"=>"#ff6347",
		"transparent"=>"#00000000",
		"turquoise"=>"#40e0d0",
		"violet"=>"#ee82ee",
		"wheat"=>"#f5deb3",
		"whitesmoke"=>"#f5f5f5",
		"yellowgreen"=>"#9acd32"
	];
	public static function hex_to_oklch(string $hex): array {
		// # を除去
		$hex = ltrim($hex, '#');

		if (strlen($hex) !== 6) {
			throw new InvalidArgumentException('Invalid hex color');
		}

		// HEX → sRGB (0–1)
		$r = hexdec(substr($hex, 0, 2)) / 255;
		$g = hexdec(substr($hex, 2, 2)) / 255;
		$b = hexdec(substr($hex, 4, 2)) / 255;

		// sRGB → Linear RGB
		$linear = function ($c) {
			return ($c <= 0.04045)
				? $c / 12.92
				: pow(($c + 0.055) / 1.055, 2.4);
		};

		$r = $linear($r);
		$g = $linear($g);
		$b = $linear($b);

		// Linear RGB → OKLab
		$l = 0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b;
		$m = 0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b;
		$s = 0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b;

		$l_ = cbrt($l);
		$m_ = cbrt($m);
		$s_ = cbrt($s);

		$L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
		$a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
		$b = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

		// OKLab → OKLCH
		$C = sqrt($a * $a + $b * $b);
		$H = rad2deg(atan2($b, $a));
		if ($H < 0) {
			$H += 360;
		}

		return [
			'l' => $L,     // 0–1
			'c' => $C,     // 通常 0–0.4 程度
			'h' => $H,     // 0–360
		];
	}
	public static function oklch_to_hex(Array $lch): string {
		$L=$lch['l'];
		$C=$lch['c'];
		$H=$lch['h'];
		// H をラジアンに変換
		$hRad = deg2rad($H);

		// OKLCH → OKLab
		$a = $C * cos($hRad);
		$b = $C * sin($hRad);

		// OKLab → LMS
		$l_ = $L + 0.3963377774 * $a + 0.2158037573 * $b;
		$m_ = $L - 0.1055613458 * $a - 0.0638541728 * $b;
		$s_ = $L - 0.0894841775 * $a - 1.2914855480 * $b;

		$l = $l_ ** 3;
		$m = $m_ ** 3;
		$s = $s_ ** 3;

		// LMS → Linear RGB
		$r =  4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
		$g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
		$b = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

		// Linear RGB → sRGB
		$to_srgb = function ($c) {
			return ($c <= 0.0031308)
				? 12.92 * $c
				: 1.055 * pow($c, 1 / 2.4) - 0.055;
		};

		$r = $to_srgb($r);
		$g = $to_srgb($g);
		$b = $to_srgb($b);

		// gamut clipping
		$r = min(max($r, 0), 1);
		$g = min(max($g, 0), 1);
		$b = min(max($b, 0), 1);

		// sRGB → HEX
		return sprintf(
			'#%02X%02X%02X',
			(int) round($r * 255),
			(int) round($g * 255),
			(int) round($b * 255)
		);
	}
}