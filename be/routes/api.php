<?php

/**
 * API Route Map
 *
 * All routes return JSON. Consumed by JS fetch() from FE pages.
 *
 * Pattern: POST /be/controllers/{Controller}.php?action={action}
 *
 * AUTH (public)
 *   POST /be/controllers/AuthController.php          action=login
 *   POST /be/controllers/AuthController.php          action=register
 *   POST /be/controllers/AuthController.php          action=logout
 *   POST /be/controllers/ForgotPasswordController.php action=send_otp
 *   POST /be/controllers/ForgotPasswordController.php action=verify_otp
 *   POST /be/controllers/ForgotPasswordController.php action=reset_pwd
 *
 * MOVIES (public)
 *   GET  /be/controllers/MovieController.php         action=list
 *   GET  /be/controllers/MovieController.php         action=detail&id={id}
 *   GET  /be/controllers/MovieController.php         action=search&q={keyword}
 *
 * BOOKINGS (requires AuthMiddleware)
 *   POST /be/controllers/BookingController.php       action=create
 *   GET  /be/controllers/BookingController.php       action=my_tickets
 *   POST /be/controllers/BookingController.php       action=cancel
 *
 * VOUCHERS (requires AuthMiddleware)
 *   POST /be/controllers/VoucherController.php       action=validate
 *   GET  /be/controllers/VoucherController.php       action=my_vouchers
 *
 * USER (requires AuthMiddleware)
 *   GET  /be/controllers/UserController.php          action=profile
 *   POST /be/controllers/UserController.php          action=update_profile
 *
 * CINEMA (public)
 *   GET  /be/controllers/CinemaController.php        action=list
 *   GET  /be/controllers/CinemaController.php        action=showtimes&id={id}
 *
 * STAFF (requires staff or admin role)
 *   GET  /be/controllers/StaffController.php         action=get_cinemas
 *   ...
 *
 * ADMIN (requires AdminMiddleware)
 *   GET  /be/controllers/admin/AdminRevenueController.php   action=dashboard_data
 *   GET  /be/controllers/admin/AdminMovieController.php     action=list
 *   POST /be/controllers/admin/AdminMovieController.php     action=create
 *   POST /be/controllers/admin/AdminMovieController.php     action=update
 *   POST /be/controllers/admin/AdminMovieController.php     action=delete
 *   ...
 *
 * NOTE: This file serves as documentation only.
 * Direct controller invocation is used (no central dispatcher).
 * If a Router is needed in the future, implement be/core/Router.php.
 */