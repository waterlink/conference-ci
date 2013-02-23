<?php

class Tests {}

function initTests($dbstr = null, $freeze = false){
	// База в памяти
	// R::setup('sqlite:/tmp/rtest.sqlite');
	if (!$dbstr){
		R::setup();
	} else {
		echo 'WARNING:: real database'."\n";
		R::setup($dbstr);
	}
	if ($freeze){
		echo 'WARNING:: freezed scheme'."\n";
		R::freeze();
	}
}

function must($cond, $desc){
	if (!$cond){
		throw new Exception($desc);
	}
}

function must_throw($func, $args, $desc){
	try {
		$func($args);
	} catch (exception $e){
		return;
	}
	throw new Exception($desc);
}

$debug_is_on = false;

function debug($x){
	global $debug_is_on;
	if ($debug_is_on){
		echo $x."\n";
	}
}

function debug_turn_on(){
	global $debug_is_on;
	$debug_is_on = true;
}

// ==================================== test functionality =======================

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

$tests = array();
$test_names = array();
$num = 0;
$ok = 0;
$inited = false;
$test_time = null;

function runTests($dbstr = null, $freeze = false){
	global $tests, $num, $ok, $inited, $test_time, $test_names;
	if (!$inited){
		echo "\n";
		initTests($dbstr, $freeze);
		$num = 0;
		$ok = 0;
	}
	// $num = 0;
	// $ok = 0;
	if (!function_exists('error_handler')){
		function error_handler($errno, $errstr, $errfile, $errline){
			global $num, $tests;
			echo "FATAL: $errstr, $errfile ($errline)\n";
			if ($num <= count($tests))
				runTests();
			return True;
		}
		function shutdown(){
			global $num, $tests;
			if ($num <= count($tests))
				runTests();
			return True;
		}
		set_error_handler('error_handler');
	}
	set_error_handler('error_handler');
	register_shutdown_function('shutdown');
	if (!$inited){
		foreach (get_declared_classes() as $c){
			if (startsWith($c, 'test_')){
				$tests[] = new $c();
				$test_names[] = $c;
			}
		}
	}
	$inited = true;
	while ($num < count($tests)){
		$num++;
		$test = $tests[$num-1];
		$test_name = $test_names[$num-1];
		$test_str = "Test $num: $test_name...";
		echo $test_str;
		$test->setup();
		try {
			$test_time = -microtime(true);
			$test->test();
			$test_time += microtime(true);
			$time_format = sprintf("%%%ds %%.3lf s", 120 - strlen($test_str));
			$test_time_str = sprintf($time_format, 'OK in', $test_time);
			echo "$test_time_str\n";
			$ok++;
		} catch (exception $e){
			echo "FAIL\n";
			echo $e->getMessage();
			echo "\n\n";
		}
		$test->teardown();
	}
	$num++;
	$cnt = count($tests);
	echo "\n=========================\n";
	echo "Passed: $ok / $cnt\n\n";
}

