<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 'landing');
        return $this->page($request, $page);
    }

    public function page(Request $request, $page = 'landing')
    {
        // To precisely match the legacy PHP behavior, we can include the files directly.
        // We will mock the required behavior from the old index.php

        // Start output buffering
        ob_start();

        // Emulate $_GET['page']
        $_GET['page'] = $page;

        // Note: the resources/views/pages/ contain the legacy PHP logic.
        $base_path = resource_path('views/pages');

        // Load legacy core logic (Database, Functions, etc.)
        if (file_exists($base_path . '/core.php')) {
            require_once $base_path . '/core.php';
        }

        // Handle AJAX requests early to prevent layout leakage
        if ($request->has('ajax') || $request->has('action')) {
            if (file_exists($base_path . '/ajax_handler.blade.php')) {
                require_once $base_path . '/ajax_handler.blade.php';
            }
            $content = ob_get_clean();
            return response($content);
        }

        // Standard Page Load
        if (file_exists($base_path . '/layout_header.blade.php')) {
            require_once $base_path . '/layout_header.blade.php';
        }

        switch ($page) {
            case 'landing':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/landing.blade.php';
                break;
            case 'login':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/login.blade.php';
                break;
            case 'register':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/register.blade.php';
                break;
            case 'forgot-password':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/forgot-password.blade.php';
                break;
            case 'verify-otp':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/verify-otp.blade.php';
                break;
            case 'reset-password':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/reset-password.blade.php';
                break;
            case 'presensi-masuk':
            case 'presensi-pulang':
                require $base_path . '/layout_html_header.blade.php';
                require $base_path . '/presensi.blade.php';
                break;
            case 'logout':
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
                return redirect('/');
            default:
                // Legacy required Auth check:
                if (!isset($_SESSION['user'])) {
                    // Redirect to login if requireAuth fails
                    ob_end_clean();
                    return redirect('/login');
                }
                require $base_path . '/layout_html_header.blade.php';
                
                if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
                    require $base_path . '/admin.blade.php';
                } else {
                    require $base_path . '/pegawai.blade.php';
                }
                break;
        }

        if (file_exists($base_path . '/layout_footer.blade.php')) {
            require $base_path . '/layout_footer.blade.php';
        }

        $content = ob_get_clean();

        return response($content);
    }
}
