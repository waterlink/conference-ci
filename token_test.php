<?php

$statements = array(
T_AND_EQUAL,
T_ARRAY,
T_ARRAY_CAST,
T_AS,
T_BOOLEAN_AND,
T_BOOLEAN_OR,
T_BOOL_CAST,
T_BREAK,
T_CALLABLE,
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
T_DOUBLE_ARROW,
T_DOUBLE_CAST,
T_DOUBLE_COLON,
T_ECHO,
T_ELSE,
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
T_INSTEADOF,
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
T_RETURN,
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
T_INTERFACE
);

$stop_statements = array(
T_FUNCTION,
T_CLASS,
T_INTERFACE
);

$contents = file_get_contents('application/models/auth.php');
$tokens = token_get_all($contents);
$real_tokens = array();
foreach ($tokens as $token){
	// var_dump($token);
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
var_dump($tokens);
echo json_encode($tokens)."\n";
