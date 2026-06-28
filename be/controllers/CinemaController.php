<?php

require_once BASE_PATH . '/be/services/CinemaService.php';
require_once BASE_PATH . '/be/core/Response.php';

class CinemaController
{
    private CinemaService $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new CinemaService($pdo);
    }

    public function list(): void
    {
        $cinemas = $this->service->getAll();
        Response::json(['success' => true, 'data' => $cinemas]);
    }

    public function showtimes(): void
    {
        $cinemaId = (int) ($_REQUEST['cinema_id'] ?? 0);

        if ($cinemaId <= 0) {
            Response::error('Thiếu mã rạp.');
            return;
        }

        $showtimes = $this->service->getShowtimesGroupedByDateAndMovie($cinemaId);

        Response::json(['success' => true, 'data' => $showtimes]);
    }
}
