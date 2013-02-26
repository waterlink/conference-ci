<?php

class Tests {}

function initTests($dbstr = null, $freeze = false){
	global $coverage_is_on;
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
	if ($coverage_is_on){
		xdebug_start_code_coverage();
	} else {
		if(function_exists('xdebug_disable')) { xdebug_disable(); }
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
$coverage_is_on = false;

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

function coverage_turn_on(){
	global $coverage_is_on;
	echo 'INFO: Turning code coverage on';
	$coverage_is_on = true;
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
	global $tests, $num, $ok, $inited, $test_time, $test_names, $coverage_is_on;
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
	// echo "\n=====Code Coverage=======\n";
	// var_dump(xdebug_get_code_coverage());
	if ($coverage_is_on){
		$coverage = xdebug_get_code_coverage();
		$hashes = array();
		foreach ($coverage as $filename => &$filecoverage){
			$content = file_get_contents($filename);
			$hash = hash('sha256', $content);
			$hashes[$filename] = $hash;
			$statements = getLineWithStatements($filename);
			foreach ($statements as $line => $tokens){
				if (!isset($filecoverage[$line])){
					$filecoverage[$line] = 0;
				}
			}
		}
		$f = fopen('../.code.coverage', 'w');
		fprintf($f, "%s\n", json_encode($coverage));
		fclose($f);
		$f = fopen('../.code.hashes', 'w');
		fprintf($f, "%s\n", json_encode($hashes));
		fclose($f);
	}
	echo "\n=========================\n";
	echo "Passed: $ok / $cnt\n\n";
}

# ---------------- Tokenizer ----------------

function getLineWithStatements($filename){
	// global $statements, $stop_statements;

$statements = array(
T_AND_EQUAL,
T_ARRAY,
T_ARRAY_CAST,
T_AS,
T_BOOLEAN_AND,
T_BOOLEAN_OR,
T_BOOL_CAST,
T_BREAK,
// T_CALLABLE,
T_CASE,
T_CATCH,
T_CLONE,
T_CONCAT_EQUAL,
T_CONSTANT_ENCAPSED_STRING,
T_CONTINUE,
T_CURLY_OPEN,
T_DEC,
T_DIV_EQUAL,
T_DNUMBER,
T_DO,
T_DOLLAR_OPEN_CURLY_BRACES,
T_DOUBLE_CAST,
T_DOUBLE_COLON,
T_ECHO,
// T_ELSE,
T_ELSEIF,
T_EMPTY,
T_ENCAPSED_AND_WHITESPACE,
T_ENDFOR,
T_ENDFOREACH,
T_ENDIF,
T_ENDSWITCH,
T_ENDWHILE,
T_END_HEREDOC,
T_EVAL,
T_EXIT,
T_FINAL,
T_FOR,
T_FOREACH,
T_GOTO,
T_HALT_COMPILER,
T_IF,
T_INC,
T_INSTANCEOF,
// T_INSTEADOF,
T_INT_CAST,
T_ISSET,
T_IS_EQUAL,
T_IS_GREATER_OR_EQUAL,
T_IS_IDENTICAL,
T_IS_NOT_EQUAL,
T_IS_NOT_IDENTICAL,
T_IS_SMALLER_OR_EQUAL,
T_LIST,
T_LNUMBER,
T_LOGICAL_AND,
T_LOGICAL_OR,
T_LOGICAL_XOR,
T_MINUS_EQUAL,
T_MOD_EQUAL,
T_MUL_EQUAL,
T_NEW,
T_NUM_STRING,
T_OBJECT_CAST,
T_OBJECT_OPERATOR,
T_OR_EQUAL,
T_PAAMAYIM_NEKUDOTAYIM,
T_PLUS_EQUAL,
T_PRINT,
// T_RETURN,
T_SL,
T_SL_EQUAL,
T_SR,
T_SR_EQUAL,
T_START_HEREDOC,
T_STRING,
T_STRING_CAST,
T_STRING_VARNAME,
T_SWITCH,
T_THROW,
T_TRY,
T_UNSET,
T_UNSET_CAST,
T_VARIABLE,
T_WHILE,
T_XOR_EQUAL,

// stops:

T_FUNCTION,
T_CLASS,
T_INTERFACE,
T_PRIVATE,
T_PROTECTED,
T_PUBLIC,
T_GLOBAL,
T_DOUBLE_ARROW
);

$stop_statements = array(
T_FUNCTION,
T_CLASS,
T_INTERFACE,
T_PRIVATE,
T_PROTECTED,
T_PUBLIC,
T_GLOBAL,
T_DOUBLE_ARROW
);

	$contents = file_get_contents($filename);
	$tokens = token_get_all($contents);
	$real_tokens = array();
	foreach ($tokens as $token){
		// var_dump($token);
		if (is_array($token)){
			$tok_type = $token[0];
			$tok_str = $token[1];
			$tok_line = $token[2];
			if (in_array($tok_type, $statements)){
				if (!isset($real_tokens["$tok_line"])){
					$real_tokens["$tok_line"] = array();
				}
				$real_tokens["$tok_line"][] = array($tok_str, $tok_type);
			}
		}
	}
	$tokens = array();
	foreach ($real_tokens as $line => $toks){
		$new_toks = array();
		foreach ($toks as $tok){
			$tok_str = $tok[0];
			$tok_type = $tok[1];
			if (in_array($tok_type, $stop_statements)){
				unset($real_tokens[$line]);
				$new_toks = array();
				break;
			}
			$new_toks[] = $tok_str;
		}
		if (isset($line, $real_tokens)){
			if ($new_toks){
				$tokens[$line] = $new_toks;
			}
		}
	}
	return $tokens;
}

