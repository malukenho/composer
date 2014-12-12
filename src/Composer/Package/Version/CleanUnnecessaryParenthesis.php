<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Package\Version;

/**
 * Clear a string removing unnecesary parenthesis
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 */
class CleanUnnecessaryParenthesis
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $version;

    /**
     * @var integer
     */
    private $lenght;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var boolean
     */
    private $first;

    /**
     * @var boolean
     */
    private $last;

    /**
     * Constructor.
     */
    private function __construct($input, Lexer $lexer)
    {
        $this->last     = false;
        $this->lexer    = $lexer;
        $this->input    = $input;
        $this->first    = false;
        $this->lenght   = strlen($input) - 1;
        $this->version  = '';
        $this->quantity = 0;

        $this->filterParenthesis();
    }

    public static function removeOn($version, Lexer $lexer)
    {
        $cleanParenthesis = new self($version, $lexer);
        return $cleanParenthesis->getString();
    }

    /**
     * Localize and remove parenthesis unnecessaries on begin and close string version
     *
     * @return void
     */
    private function filterParenthesis()
    {
        while (true) {
            $this->lexer->moveNext();

            if ($this->lexer->tokenIsOpenParenthesis()) {
                $this->countIncrease();
                continue;
            }

            if ($this->lexer->tokenIsCloseParenthesis()) {
                $this->countDecrease();
                continue;
            }

            $this->version .= $this->lexer->token['value'];

            if (! $this->lexer->lookahead) {
                $this->resetVersionIfInvalid();
                break;
            }
        }
    }

    /**
     * Count increase on open parenthesis.
     *
     * @return void
     */
    protected function countIncrease()
    {
        $this->quantity++;

        if (!$this->first && 0 === $this->lexer->token['position']
            && $this->lexer->tokenIsOpenParenthesis()
        ) {
            $this->first = true;
            return true;
        }

        $this->version .= $this->lexer->token['value'];
    }

    /**
     * Count decrease on close parenthesis.
     *
     * @return void|null
     */
    protected function countDecrease()
    {
        $this->quantity--;

        if (0 !== $this->quantity) {
            $this->version .= $this->lexer->token['value'];
            return;
        }

        if ($this->lenght === $this->lexer->token['position']
            && $this->lexer->tokenIsCloseParenthesis()
        ) {
           $this->last = true;
           return;
        }

        $this->version = $this->input;
    }

    /**
     * If generate a invaid or wrong version.
     * We can set version as original string given.
     *
     * @return void
     */
    private function resetVersionIfInvalid()
    {
        if (! $this->last || !$this->first) {
            $this->version = $this->input;
        }
    }

    /**
     * Get the version without parenthesis
     *
     * @return string
     */
    public function getString()
    {
        return $this->version;
    }
}
