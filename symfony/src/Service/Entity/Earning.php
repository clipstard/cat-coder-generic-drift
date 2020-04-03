<?php

namespace App\Entity\Service;


class Earning
{
    /** @var int */
    protected $amount;
    /** @var string */
    protected $destination;

    /** @var int */
    protected $day;

    /** @var int */
    protected $id;

    /** @var int|null */
    protected $driver;

    /**
     * @var bool
     */
    protected $active = true;

    /** @var int|null */
    protected $from;
    /** @var int|null */
    protected $to;

    public function __construct($destination = null, $day = null, $amount = null)
    {
        $this->destination = $destination;
        $this->day = $day;
        $this->amount = $amount;
    }

    /**
     * @param int|null $from
     *
     * @return Earning
     */
    public function setFrom(?int $from): Earning
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDriver(): ?int
    {
        return $this->driver;
    }

    /**
     * @param int|null $driver
     *
     * @return Earning
     */
    public function setDriver(?int $driver): Earning
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param int|null $to
     *
     * @return Earning
     */
    public function setTo(?int $to): Earning
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFrom(): ?int
    {
        return $this->from;
    }

    /**
     * @return int|null
     */
    public function getTo(): ?int
    {
        return $this->to;
    }

    public function isInDayFrame(int $date)
    {
        return $this->getFrom() > $date && $this->getTo() < $date;
    }

    public function __toString(): string
    {
        return "{$this->destination} {$this->day} {$this->to} {$this->amount}";
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Earning
     */
    public function setId(int $id): Earning
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return Earning
     */
    public function setAmount(int $amount): Earning
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDay(): ?int
    {
        return $this->day;
    }

    /**
     * @param int $day
     *
     * @return Earning
     */
    public function setDay(int $day): Earning
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @return Earning
     */
    public function setDestination(string $destination): Earning
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @param bool $active
     *
     * @return Earning
     */
    public function setActive(bool $active): Earning
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param Earning[] $earnings
     * @param string $parameter
     */
    public static function orderBy(&$earnings, $parameter = 'day')
    {
        $parameter = ucfirst($parameter);
        try {
            $e = (new self())->{'get' . $parameter}();
        } catch (\Exception $exception) {
            return;
        }

        $c = count($earnings);
        for ($i = 0; $i < $c - 1; $i++) {
            for ($j = $i + 1; $j < $c; $j++) {
                if ($earnings[$i]->{'get' . $parameter}() > $earnings[$j]->{'get' . $parameter}()) {
                    [$earnings[$i], $earnings[$j]] = [$earnings[$j], $earnings[$i]];
                }
            }
        }
    }
}
