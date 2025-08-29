<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Show login form */
    public function showLogin()
    {
        // If already logged in, go to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /** Handle login with legacy-hash upgrade (MD5 -> bcrypt) */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
        ]);

        $remember = (bool) $request->boolean('remember');

        // Try to find the user by legacy Username column
        $user = User::where('Username', $credentials['username'])->first();
        if ($user) {
            $stored = (string) $user->PasswordHash;

            // Detect bcrypt by prefix; otherwise treat as legacy (e.g., MD5)
            $isBcrypt = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2b$');

            if ($isBcrypt) {
                if (Hash::check($credentials['password'], $stored)) {
                    Auth::login($user, $remember);
                    $request->session()->regenerate();
                    return redirect()->intended(route('dashboard'));
                }
            } else {
                // Legacy check: common case is 32-char hex MD5
                $md5 = md5($credentials['password']);
                if (strcasecmp($md5, $stored) === 0) {
                    // Upgrade hash to bcrypt and login
                    $user->PasswordHash = Hash::make($credentials['password']);
                    $user->save();

                    Auth::login($user, $remember);
                    $request->session()->regenerate();
                    return redirect()->intended(route('dashboard'));
                }
            }
        }

        // Fallback: invalid credentials
        return back()->withErrors([
            'username' => 'بيانات تسجيل الدخول غير صحيحة.',
        ])->onlyInput('username');
    }

    /** Logout */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /** Enhanced dashboard: provide stats and summaries for the view */
    public function dashboard()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Stats
        $stats = [
            'total_sales' => (float) \DB::table('transactions')
                ->where('TransactionType', 'Sale')
                ->whereBetween('TransactionDate', [$startOfMonth, $endOfMonth])
                ->sum('TotalAmount'),
            'total_products' => (int) \DB::table('products')->count(),
            'total_customers' => (int) \DB::table('accounts')->where('AccountType','Customer')->count(),
            'total_inventory' => (int) \DB::table('products')->sum('StockByUnit'), // وحدات
        ];

        // Recent sales (map to the expected keys used by the blade)
        $recent_sales_raw = \DB::table('transactions as t')
            ->leftJoin('accounts as a', 'a.AccountID', '=', 't.AccountID')
            ->where('t.TransactionType', 'Sale')
            ->orderByDesc('t.TransactionDate')
            ->limit(10)
            ->get(['t.TransactionNumber','t.TransactionDate','t.TotalAmount','a.AccountName']);

        $recent_sales = $recent_sales_raw->map(function($row){
            return (object) [
                'InvoiceNumber' => $row->TransactionNumber,
                'customer' => (object) ['CustomerName' => $row->AccountName],
                'SaleDate' => $row->TransactionDate,
                'GrandTotal' => $row->TotalAmount,
                'Status' => 'Completed',
            ];
        });

        // Top products by quantity and revenue from sales
        $top_products = \DB::table('transactiondetails as d')
            ->join('transactions as t', 't.TransactionID', '=', 'd.TransactionID')
            ->join('products as p', 'p.ProductID', '=', 'd.ProductID')
            ->where('t.TransactionType', 'Sale')
            ->groupBy('d.ProductID','p.ProductName')
            ->orderByDesc(\DB::raw('SUM(d.Quantity)'))
            ->limit(5)
            ->get([
                'd.ProductID',
                'p.ProductName',
                \DB::raw('SUM(d.Quantity) as total_quantity'),
                \DB::raw('SUM(d.LineTotal) as total_revenue'),
            ])->map(function($row){
                return (object) [
                    'product' => (object) ['ProductName' => $row->ProductName],
                    'total_quantity' => (float) $row->total_quantity,
                    'total_revenue' => (float) $row->total_revenue,
                ];
            });

        return view('dashboard', compact('stats','recent_sales','top_products'));
    }
}