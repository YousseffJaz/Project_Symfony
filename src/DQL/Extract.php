<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Extract extends FunctionNode
{
    private $field;
    private $value;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        
        // Get field (DAY, MONTH, etc.)
        $parser->match(Lexer::T_IDENTIFIER);
        $this->field = $parser->getLexer()->token->value;
        
        $parser->match(Lexer::T_FROM);
        
        // Get datetime expression
        $this->value = $parser->ArithmeticPrimary();
        
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('EXTRACT(%s FROM %s)',
            strtoupper($this->field),
            $this->value->dispatch($sqlWalker)
        );
    }
} 