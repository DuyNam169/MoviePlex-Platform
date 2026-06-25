<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'admin_monitor'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'dashboard_data';

if ($action === 'dashboard_data') {
    $response = [];
    
    // Real dynamic KPIs calculation (Today's Totals)
    $total_tickets = (int)$pdo->query("SELECT SUM(num_tickets) FROM bookings WHERE status != 'cancelled' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
    $total_checkins = (int)$pdo->query("SELECT SUM(num_tickets) FROM bookings WHERE status = 'checked_in' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
    $total_revenue = (float)$pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status != 'cancelled' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
    $active_staff = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn() ?: 0;
    $locked_accounts = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'locked'")->fetchColumn() ?: 0;
    
    // Calculate dynamic trends today vs yesterday
    $stats_today_yesterday = $pdo->query("
        SELECT 
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN num_tickets ELSE 0 END) as t_today,
            SUM(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY THEN num_tickets ELSE 0 END) as t_yesterday,
            SUM(CASE WHEN DATE(created_at) = CURDATE() AND status = 'checked_in' THEN num_tickets ELSE 0 END) as c_today,
            SUM(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'checked_in' THEN num_tickets ELSE 0 END) as c_yesterday,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as r_today,
            SUM(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY THEN total_amount ELSE 0 END) as r_yesterday
        FROM bookings
        WHERE status != 'cancelled'
    ")->fetch();

    $t_today = (int)($stats_today_yesterday['t_today'] ?? 0);
    $t_yesterday = (int)($stats_today_yesterday['t_yesterday'] ?? 0);
    $c_today = (int)($stats_today_yesterday['c_today'] ?? 0);
    $c_yesterday = (int)($stats_today_yesterday['c_yesterday'] ?? 0);
    $r_today = (float)($stats_today_yesterday['r_today'] ?? 0);
    $r_yesterday = (float)($stats_today_yesterday['r_yesterday'] ?? 0);

    if (!function_exists('calcTrend')) {
        function calcTrend($today, $yesterday) {
            if ($yesterday == 0) {
                if ($today > 0) {
                    return ['text' => '+100% so với hôm qua', 'class' => 'positive'];
                }
                return ['text' => '0% so với hôm qua', 'class' => 'neutral'];
            }
            $diff = (($today - $yesterday) / $yesterday) * 100;
            $diff_formatted = number_format(abs($diff), 1, ',', '.');
            if ($diff > 0) {
                return ['text' => '+' . $diff_formatted . '% so với hôm qua', 'class' => 'positive'];
            } elseif ($diff < 0) {
                return ['text' => '-' . $diff_formatted . '% so với hôm qua', 'class' => 'negative'];
            } else {
                return ['text' => '0% so với hôm qua', 'class' => 'neutral'];
            }
        }
    }

    $tickets_trend = calcTrend($t_today, $t_yesterday);
    $checkins_trend = calcTrend($c_today, $c_yesterday);
    $revenue_trend = calcTrend($r_today, $r_yesterday);

    $response['kpis'] = [
        'total_tickets' => $total_tickets,
        'total_checkins' => $total_checkins,
        'total_revenue' => $total_revenue,
        'active_staff' => $active_staff,
        'locked_accounts' => $locked_accounts,
        'tickets_trend' => $tickets_trend['text'],
        'tickets_trend_class' => $tickets_trend['class'],
        'checkins_trend' => $checkins_trend['text'],
        'checkins_trend_class' => $checkins_trend['class'],
        'revenue_trend' => $revenue_trend['text'],
        'revenue_trend_class' => $revenue_trend['class']
    ];
    
    // Real sales trend for the last 7 days (guaranteed date points in order)
    $sales_trend = [];
    for ($i = 6; $i >= 0; $i--) {
        $date_str = date('Y-m-d', strtotime("-$i days"));
        $day_name = date('d/m', strtotime("-$i days"));
        
        $stmt = $pdo->prepare("
            SELECT SUM(num_tickets) 
            FROM bookings 
            WHERE status != 'cancelled' AND DATE(created_at) = ?
        ");
        $stmt->execute([$date_str]);
        $tickets_sold = (int)$stmt->fetchColumn() ?: 0;
        
        $sales_trend[] = [
            'day_name' => $day_name,
            'tickets_sold' => $tickets_sold
        ];
    }
    $response['sales_trend'] = $sales_trend;
    
    // Real check-in hourly for today (grouped by 2-hour slots)
    $checkin_hourly = [];
    $hours = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
    foreach ($hours as $h) {
        $start_hour = (int)substr($h, 0, 2);
        $stmt = $pdo->prepare("
            SELECT SUM(num_tickets) 
            FROM bookings 
            WHERE status = 'checked_in' 
              AND DATE(created_at) = CURDATE() 
              AND HOUR(created_at) >= ? 
              AND HOUR(created_at) < ?
        ");
        $stmt->execute([$start_hour, $start_hour + 2]);
        $checkins = (int)$stmt->fetchColumn() ?: 0;
        
        $checkin_hourly[] = [
            'hour_label' => $h,
            'checkins' => $checkins
        ];
    }
    $response['checkin_hourly'] = $checkin_hourly;
    
    // Find dynamic peak check-in hour today
    $peak_hour = '19:30';
    $peak_count = 0;
    $peak_stmt = $pdo->query("
        SELECT HOUR(created_at) as hr, SUM(num_tickets) as checkins
        FROM bookings
        WHERE status = 'checked_in' AND DATE(created_at) = CURDATE()
        GROUP BY HOUR(created_at)
        ORDER BY checkins DESC
        LIMIT 1
    ");
    $peak = $peak_stmt->fetch();
    if ($peak && $peak['checkins'] > 0) {
        $peak_hour = sprintf('%02d:00', $peak['hr']);
        $peak_count = (int)$peak['checkins'];
    }
    
    $response['checkin_peak'] = [
        'hour' => $peak_hour,
        'count' => $peak_count
    ];
    
    // Only get 4 recent logs for dashboard
    $response['logs'] = $pdo->query("SELECT * FROM system_logs ORDER BY id DESC LIMIT 4")->fetchAll();
    $response['errors'] = []; 
    
    echo json_encode($response);
    exit;
}

if ($action === 'get_logs') {
    $logs = $pdo->query("SELECT * FROM system_logs ORDER BY id DESC")->fetchAll();
    echo json_encode(['success' => true, 'data' => $logs]);
    exit;
}

if ($action === 'get_employees') {
    $employees = $pdo->query("SELECT * FROM employees ORDER BY id ASC")->fetchAll();
    // Count stats
    $total = count($employees);
    $active = 0; $locked = 0;
    foreach($employees as $emp) {
        if($emp['status'] == 'active') $active++;
        if($emp['status'] == 'locked') $locked++;
    }
    echo json_encode([
        'success' => true, 
        'data' => $employees,
        'stats' => [
            'total' => $total,
            'active' => $active,
            'locked' => $locked,
            'pending' => 4 // Hardcoded for mockup
        ]
    ]);
    exit;
}

if ($action === 'get_revenue_report') {
    try {
        $startDate = $_GET['startDate'] ?? '';
        $endDate = $_GET['endDate'] ?? '';

        if (!empty($startDate) && !empty($endDate)) {
            $filter = 'custom';
        } else {
            $filter = $_GET['filter'] ?? 'today';
            if ($filter === 'week') {
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));
            } elseif ($filter === 'month') {
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
            } elseif ($filter === 'year') {
                $startDate = date('Y-01-01');
                $endDate = date('Y-12-31');
            } else {
                $filter = 'today';
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
            }
        }

        $safe_start = $pdo->quote($startDate);
        $safe_end = $pdo->quote($endDate);

        $where_time = "DATE(created_at) BETWEEN $safe_start AND $safe_end";
        $where_time_b = "DATE(b.created_at) BETWEEN $safe_start AND $safe_end";

        // 1. Overall stats
        $total_revenue = (float)($pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status != 'cancelled' AND $where_time")->fetchColumn() ?: 0);
        $total_tickets = (int)($pdo->query("SELECT SUM(num_tickets) FROM bookings WHERE status != 'cancelled' AND $where_time")->fetchColumn() ?: 0);
        $total_bookings = (int)($pdo->query("SELECT COUNT(*) FROM bookings WHERE status != 'cancelled' AND $where_time")->fetchColumn() ?: 0);

        $ticket_revenue = (float)($pdo->query("SELECT SUM(subtotal) FROM bookings WHERE status != 'cancelled' AND $where_time")->fetchColumn() ?: 0);
        $snack_revenue = (float)($pdo->query("SELECT SUM(GREATEST(0, total_amount - subtotal + discount)) FROM bookings WHERE status != 'cancelled' AND $where_time")->fetchColumn() ?: 0);

        // Online stats
        $online_revenue = (float)($pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status != 'cancelled' AND payment_method != 'cash' AND $where_time")->fetchColumn() ?: 0);
        $online_tickets = (int)($pdo->query("SELECT SUM(num_tickets) FROM bookings WHERE status != 'cancelled' AND payment_method != 'cash' AND $where_time")->fetchColumn() ?: 0);
        $online_bookings = (int)($pdo->query("SELECT COUNT(*) FROM bookings WHERE status != 'cancelled' AND payment_method != 'cash' AND $where_time")->fetchColumn() ?: 0);
        $online_ticket_revenue = (float)($pdo->query("SELECT SUM(subtotal) FROM bookings WHERE status != 'cancelled' AND payment_method != 'cash' AND $where_time")->fetchColumn() ?: 0);
        $online_snack_revenue = (float)($pdo->query("SELECT SUM(GREATEST(0, total_amount - subtotal + discount)) FROM bookings WHERE status != 'cancelled' AND payment_method != 'cash' AND $where_time")->fetchColumn() ?: 0);

        // Direct / Counter stats
        $direct_revenue = (float)($pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status != 'cancelled' AND payment_method = 'cash' AND $where_time")->fetchColumn() ?: 0);
        $direct_tickets = (int)($pdo->query("SELECT SUM(num_tickets) FROM bookings WHERE status != 'cancelled' AND payment_method = 'cash' AND $where_time")->fetchColumn() ?: 0);
        $direct_bookings = (int)($pdo->query("SELECT COUNT(*) FROM bookings WHERE status != 'cancelled' AND payment_method = 'cash' AND $where_time")->fetchColumn() ?: 0);
        $direct_ticket_revenue = (float)($pdo->query("SELECT SUM(subtotal) FROM bookings WHERE status != 'cancelled' AND payment_method = 'cash' AND $where_time")->fetchColumn() ?: 0);
        $direct_snack_revenue = (float)($pdo->query("SELECT SUM(GREATEST(0, total_amount - subtotal + discount)) FROM bookings WHERE status != 'cancelled' AND payment_method = 'cash' AND $where_time")->fetchColumn() ?: 0);

        // 2. Revenue by Movie
        $movie_revenue = $pdo->query("
            SELECT m.title, m.poster_url, COUNT(b.id) as bookings_count, SUM(b.num_tickets) as tickets_count, SUM(b.total_amount) as revenue
            FROM bookings b
            JOIN showtimes s ON b.showtime_id = s.id
            JOIN movies m ON s.movie_id = m.id
            WHERE b.status != 'cancelled' AND $where_time_b
            GROUP BY m.id
            ORDER BY revenue DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Revenue by Cinema
        $cinema_revenue = $pdo->query("
            SELECT c.name as cinema_name, COUNT(b.id) as bookings_count, SUM(b.num_tickets) as tickets_count, SUM(b.total_amount) as revenue
            FROM bookings b
            JOIN showtimes s ON b.showtime_id = s.id
            JOIN cinemas c ON s.cinema_id = c.id
            WHERE b.status != 'cancelled' AND $where_time_b
            GROUP BY c.id
            ORDER BY revenue DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Detailed Bookings
        $detailed_bookings = $pdo->query("
            SELECT b.*, u.full_name as customer_name, m.title as movie_title, c.name as cinema_name
            FROM bookings b
            LEFT JOIN users u ON b.user_id = u.id
            JOIN showtimes s ON b.showtime_id = s.id
            JOIN movies m ON s.movie_id = m.id
            JOIN cinemas c ON s.cinema_id = c.id
            WHERE b.status != 'cancelled' AND $where_time_b
            ORDER BY b.created_at DESC
            LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 5. Daily Sales Trend
        if ($startDate === $endDate) {
            $daily_sales = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%H:00') as day_label, 
                    SUM(CASE WHEN payment_method != 'cash' THEN total_amount ELSE 0 END) as online_revenue,
                    SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as direct_revenue,
                    SUM(total_amount) as revenue
                FROM bookings
                WHERE status != 'cancelled' AND DATE(created_at) = $safe_start
                GROUP BY HOUR(created_at)
                ORDER BY created_at ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $daily_sales = $pdo->query("
                SELECT 
                    DATE_FORMAT(created_at, '%d/%m') as day_label, 
                    SUM(CASE WHEN payment_method != 'cash' THEN total_amount ELSE 0 END) as online_revenue,
                    SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END) as direct_revenue,
                    SUM(total_amount) as revenue
                FROM bookings
                WHERE status != 'cancelled' AND DATE(created_at) BETWEEN $safe_start AND $safe_end
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($daily_sales)) {
            $daily_sales = [];
            if ($startDate === $endDate) {
                for ($h = 8; $h <= 23; $h += 2) {
                    $daily_sales[] = [
                        'day_label' => sprintf('%02d:00', $h),
                        'online_revenue' => 0,
                        'direct_revenue' => 0,
                        'revenue' => 0
                    ];
                }
            } else {
                $curr = strtotime($startDate);
                $last = strtotime($endDate);
                $days_diff = round(($last - $curr) / 86400);
                if ($days_diff <= 31) {
                    while ($curr <= $last) {
                        $daily_sales[] = [
                            'day_label' => date('d/m', $curr),
                            'online_revenue' => 0,
                            'direct_revenue' => 0,
                            'revenue' => 0
                        ];
                        $curr = strtotime("+1 day", $curr);
                    }
                }
            }
        }

        echo json_encode([
            'success' => true,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filter' => $filter,
            'kpis' => [
                'total_revenue' => $total_revenue,
                'total_tickets' => $total_tickets,
                'total_bookings' => $total_bookings,
                'ticket_revenue' => $ticket_revenue,
                'snack_revenue' => $snack_revenue,
                'online' => [
                    'revenue' => $online_revenue,
                    'tickets' => $online_tickets,
                    'bookings' => $online_bookings,
                    'ticket_revenue' => $online_ticket_revenue,
                    'snack_revenue' => $online_snack_revenue
                ],
                'direct' => [
                    'revenue' => $direct_revenue,
                    'tickets' => $direct_tickets,
                    'bookings' => $direct_bookings,
                    'ticket_revenue' => $direct_ticket_revenue,
                    'snack_revenue' => $direct_snack_revenue
                ]
            ],
            'movie_revenue' => $movie_revenue,
            'cinema_revenue' => $cinema_revenue,
            'detailed_bookings' => $detailed_bookings,
            'daily_sales' => $daily_sales
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
