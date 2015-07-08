<?php 
class Kaleidoscope_Color_Calculator {

	public static function date_to_color( $day, $year ) {

		$red = self::color_maker( $day, $year, 20, 134 ); //18, 134
		$green = self::color_maker( $day, $year, 20, 240 ); //20, 240
		$blue = self::color_maker( $day, $year, 10, 0 ); // 10, 0
		
		return $rgb = "{$red}{$green}{$blue}";
	}

	private static function color_maker( $day, $year, $broaden = 0, $shift = 0 ) {

		$in_degree = .986 * $day; // from 365.25=>360
		
		/* pshift = 
		New degree value = incoming period shift + degree from year - sine function
		Sine Function = Random value * sine of shifted value 
			> This essentially works to make the coming cosine function stay near its peak for a while
			> based on the magnitude of the random value
		*/
		$pshift = $shift + $in_degree - ( $broaden*sin( (M_PI*( $in_degree+$shift ) )/180) ); 
		
		$year_diff = date( 'Y' ) - $year;
		$hshift = .08 * $year_diff; // to be used for further away years fading
		if ( $hshift > 1 ) { //this assures that the colors are always valid otherwise we could get negative numbers
			$hshift = 1;
		} 
		
		$HBASE = .82; //the center of the function; set between 0 and 2, lower are more saturated (darker)
		if ( $HBASE > 1 ) {
			$HSAFE = 2 - $HBASE; 
			// $hsafe is to ensure no final values greater than 2, which would create invalid colors
		} else {
			$HSAFE = $HBASE;
		}
		
		/* calced_color ==
		Use Cosine to create a weighted set of results between 0 and 2
		Multiply by 127.5 to get results between 0 and 255
		dechex for results between 0 and FF
		+Change the first (and only the first) +/- to toggle fade/darken
		*/ 
		$calced_color = dechex(127.5*(($HBASE-($HSAFE*$hshift))+(($HSAFE-($HSAFE*$hshift))*(cos((M_PI*($pshift))/180))))); 
		$hexpart = str_pad($calced_color, 2, "00", STR_PAD_LEFT);
		return $hexpart;
	}	
}