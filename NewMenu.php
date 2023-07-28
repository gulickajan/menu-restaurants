<?php

class NewMenu
{
    protected $db;
    protected string $day;
    protected string $price;
    protected string $place;
    protected string $menu;

    public function __construct(MyPDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $day
     */
    public function setDay(string $day): void
    {
        $this->day = $day;
    }

    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    /**
     * @param string $place
     */
    public function setPlace(string $place): void
    {
        $this->place = $place;
    }

    /**
     * @param string $menu
     */
    public function setMenu(string $menu): void
    {
        $this->menu = $menu;
    }


    public function save() {
        $this->db->run("INSERT INTO parsed_data (`price`, `place`, `menu`, `day`) VALUES (?, ?, ?, ?)",
            [$this->price, $this->place, $this->menu, $this->day]);
    }
}