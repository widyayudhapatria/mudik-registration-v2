<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelerListController extends Controller
{
    /**
     * Display the list of approved travelers.
     */
    public function index(Request $request): View
    {
        // Validate and sanitize all inputs to prevent XSS and SQL injection
        $validated = $request->validate([
            'destination_id' => 'nullable|integer|exists:destinations,id',
            'search_name' => 'nullable|string|max:100|regex:/^[A-Za-z\s]+$/',
        ]);

        // Get all active destinations for filter dropdown
        $destinations = Destination::active()
            ->ordered()
            ->get();

        // Build query for approved registrations
        // Select only necessary registration columns to minimize payload
        $query = Registration::select([
            'id',
            'destination_id',
            'form_link_id',
            'representative_name',
            'kk_number',
        ])->with([
            'destination' => function ($q) {
                $q->select('id', 'name');
            },
            'formLink' => function ($q) {
                $q->select('id', 'email', 'status');
            }
        ])->whereHas('formLink', function ($q) {
            $q->where('status', 'approved');
        });

        // Apply destination filter with validated input
        if (!empty($validated['destination_id'])) {
            $query->where('destination_id', $validated['destination_id']);
        }

        // Apply name search filter with sanitized input
        if (!empty($validated['search_name'])) {
            // Additional sanitization: strip_tags, trim, and escape special chars
            $searchName = htmlspecialchars(strip_tags(trim($validated['search_name'])), ENT_QUOTES, 'UTF-8');

            // Use parameterized query (Eloquent automatically escapes) to prevent SQL injection
            $query->where('representative_name', 'like', '%' . $searchName . '%');
        }

        // Get travelers with participant count, ordered alphabetically by name
        $travelers = $query
            ->withCount('participants')
            ->orderBy('representative_name', 'asc')
            ->get();

        // Mask sensitive data in backend for security
        $travelers->each(function ($traveler) {
            $traveler->masked_email = $this->maskEmail($traveler->formLink->email ?? '');
            $traveler->masked_kk = $this->maskKkNumber($traveler->kk_number ?? '');
        });

        return view('pages.daftar-pemudik', [
            'destinations' => $destinations,
            'travelers' => $travelers,
            'selectedDestination' => $request->input('destination_id'),
            'searchName' => $request->input('search_name'),
        ]);
    }

    /**
     * Mask email address for privacy.
     *
     * Example: user@example.com becomes use***r@example.com
     * Shows: 3 first chars + *** + 1 last char before @
     */
    private function maskEmail(string $email): string
    {
        if (empty($email)) {
            return '***@***.***';
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $username = $parts[0];
        $domain = $parts[1];
        $usernameLength = strlen($username);

        if ($usernameLength <= 1) {
            $maskedUsername = str_repeat('*', $usernameLength);
        } elseif ($usernameLength <= 4) {
            // For short usernames, show first 1-2 chars + ***
            $maskedUsername = substr($username, 0, min(2, $usernameLength - 1)) . '***';
        } else {
            // Show 3 first chars + *** + 1 last char
            $maskedUsername = substr($username, 0, 3) . '***' . substr($username, -1);
        }

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Mask KK number for privacy.
     *
     * Example: 1234567890123456 becomes 1234********3456
     */
    private function maskKkNumber(string $kkNumber): string
    {
        $length = strlen($kkNumber);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($kkNumber, 0, 4) . str_repeat('*', $length - 8) . substr($kkNumber, -4);
    }
}
