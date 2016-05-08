<?php


class Amount extends \oxSuperCfg
{
    /**
     * @var string
     */
    private $amount;
    public function __construct($config, $amount)
    {
        parent::__construct($config);

        $amount = str_replace(',', '.', $amount);
        if (!$this->config->getConfigParam('blAllowUnevenAmounts')) {
            $amount = round(( string ) $amount);
        }
        $this->amount = $amount;
    }

    public function __toString()
    {
        return (string) $this->amount;
    }
}