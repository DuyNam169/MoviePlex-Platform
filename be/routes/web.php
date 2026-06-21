<?php

/**
 * Web Route Map (Page Routes)
 *
 * These are PHP pages in fe/ that render HTML.
 * Apache serves fe/ as DocumentRoot.
 * Each page includes AuthMiddleware or AdminMiddleware at the top as needed.
 *
 * PUBLIC PAGES (no auth required)
 *   /fe/pages/index.php           -> redirect to home or login
 *   /fe/pages/guest.php           -> movie listing for guests
 *   /fe/pages/login.php           -> login + register form
 *   /fe/pages/forgot-password.php -> password reset wizard
 *   /fe/pages/movies.php          -> all movies
 *   /fe/pages/movie-detail.php    -> single movie + showtimes
 *   /fe/pages/cinemas.php         -> cinema listing
 *   /fe/pages/help.php            -> FAQ / support
 *
 * PROTECTED PAGES (require AuthMiddleware::page())
 *   /fe/pages/seat-select.php     -> seat picker
 *   /fe/pages/booking-confirm.php -> booking confirmation
 *   /fe/pages/checkout.php        -> payment
 *   /fe/pages/my-tickets.php      -> ticket history
 *   /fe/pages/vouchers.php        -> user vouchers
 *   /fe/pages/profile.php         -> user profile
 *
 * STAFF PAGES (require role=staff or admin)
 *   /fe/pages/staff.php           -> staff dashboard
 *
 * ADMIN PAGES (require AdminMiddleware::page())
 *   /fe/admin/index.php           -> admin dashboard
 *   /fe/admin/movies.php          -> manage movies
 *   /fe/admin/cinemas.php         -> manage cinemas
 *   /fe/admin/showtimes.php       -> manage showtimes
 *   /fe/admin/vouchers.php        -> manage vouchers
 *   /fe/admin/revenue.php         -> revenue reports
 *   /fe/admin/users.php           -> manage users
 *   /fe/admin/logs.php            -> system logs
 *   /fe/admin/reconciliation.php  -> transaction reconciliation
 *
 * NOTE: This file serves as documentation only.
 * Middleware is applied per-file at the top of each FE page.
 */