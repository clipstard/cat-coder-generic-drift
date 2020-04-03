<?php

namespace App\Entity\Service;


class Coord
{
    /** @var float|null */
    protected $lat;
    /** @var float|null */
    protected $long;
    /** @var int|null */
    protected $time;
    /** @var int|null */
    protected $alt;

    /** @var int|null */
    protected  $id;

    /** @var string|null */
    protected $from;

    /** @var string|null */
    protected $to;

    /** @var int|null */
    protected $takeoff;

    protected $count = 0;


    public function __construct($time = null, $lat = null, $long = null, $altitude = null)
    {
        $this->lat = $lat;
        $this->time = $time;
        $this->long = $long;
        $this->alt = $altitude;
    }

    /**
     * @return $this
     */
    public function inc(): self
    {
        $this->count++;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * @param string|null $to
     *
     * @return Coord
     */
    public function setTo(?string $to): Coord
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param int $count
     *
     * @return Coord
     */
    public function setCount(int $count): Coord
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @param string|null $from
     *
     * @return Coord
     */
    public function setFrom(?string $from): Coord
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAlt(): ?int
    {
        return $this->alt;
    }

    /**
     * @param int|null $alt
     *
     * @return Coord
     */
    public function setAlt(?int $alt): Coord
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTakeoff(): ?int
    {
        return $this->takeoff;
    }

    /**
     * @param int|null $takeoff
     *
     * @return Coord
     */
    public function setTakeoff(?int $takeoff): Coord
    {
        $this->takeoff = $takeoff;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     *
     * @return Coord
     */
    public function setLat(?float $lat): Coord
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLong(): ?float
    {
        return $this->long;
    }

    /**
     * @param float|null $long
     *
     * @return Coord
     */
    public function setLong(?float $long): Coord
    {
        $this->long = $long;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return Coord
     */
    public function setId(?int $id): Coord
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTime(): ?int
    {
        return $this->time;
    }

    /**
     * @param int|null $time
     *
     * @return Coord
     */
    public function setTime(?int $time): Coord
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /** @param Coord[] $coords */
    public static function order(&$coords)
    {
        $c = count($coords);
        $keys = array_keys($coords);
        for ($i = 0; $i < $c - 1; $i++) {
            for ($j = $i + 1; $j < $c; $j++) {
                if (
                    $coords[$keys[$i]]->getFrom() > $coords[$keys[$j]]->getFrom() ||
                    ($coords[$keys[$i]]->getFrom() === $coords[$keys[$j]]->getFrom() &&
                        $coords[$keys[$i]]->getTo() > $coords[$keys[$j]]->getTo()
                    )
                ) {
                    [$coords[$keys[$i]], $coords[$keys[$j]]] = [$coords[$keys[$j]], $coords[$keys[$i]]];
                }
            }
        }
    }

    /**
     * @param Coord[] $earnings
     * @param string $parameter
     */
    public static function orderBy(&$earnings, $parameter = 'time')
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
