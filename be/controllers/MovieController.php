<?php

require_once BASE_PATH . '/be/services/MovieService.php';
require_once BASE_PATH . '/be/core/Response.php';

class MovieController
{
    private MovieService $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new MovieService($pdo);
    }

    public function listByStatus(): void
    {
        $status = trim($_REQUEST['status'] ?? 'now_showing');
        $status = $status === 'coming_soon' ? 'coming_soon' : 'now_showing';

        $this->service->autoPromoteMovies();
        $movies = $this->service->getByStatus($status);

        Response::json(['success' => true, 'data' => $movies]);
    }

    public function detail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Response::error('Thiếu mã phim.');
            return;
        }

        $this->service->autoPromoteMovies();
        $movie = $this->service->getMovieDetail($id);
        if (!$movie) {
            Response::error('Không tìm thấy phim.');
            return;
        }

        $showtimes = $this->service->getShowtimesGrouped($id);
        $reviews = $this->service->getReviews($id);

        Response::json([
            'success' => true,
            'data'    => [
                'movie'     => $movie,
                'showtimes' => $showtimes,
                'reviews'   => $reviews,
            ],
        ]);
    }
}
