<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Color
 *
 * @author bgarcia
 */
class Color {//need to define this here rather than in css for excel
	public static $usageFlagged = '#738291'; //light blue
	public static $usageOverriden = '#a9c0d8'; //blue
	public static $usageMerged = '#d8d5da';
	public static $levels = array(
		array('','',''),
		array('#e9e33c','levelOne','yellow'),
		array('#e9913c"','levelTwo','orange'),
		array('#e93c3c"','levelThree','red'));
}
