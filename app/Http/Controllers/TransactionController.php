<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Transaction;
use Yajra\DataTables\DataTables;

class TransactionController extends Controller
{
    /**
     * Display the transaction list.
     */
    public function show(Request $request): View
    {

        return view('transaction.index', [
            'user' => $request->user(),
            'transactions' => Transaction::all()
        ]);
    }

    public function dailyDetail(Request $request)
    {
        $date = $request->date;
        $query = Transaction::select(
            'name',
            'transactions.created_at as start',
            'amount',
            'type',
            'tags.tagname'
        )
        ->leftJoin('tags', 'transactions.tag_id', '=', 'tags.id')
        ->where('user_id', auth()->id())
        ->whereDate('transactions.created_at', $date);

        if ($request->ajax()) {
            return DataTables::of($query)
                ->filterColumn('tagname', function($query, $keyword) {
                    $sql = "tags.tagname LIKE ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('title', function($query, $keyword) {
                    $sql = "transactions.name LIKE ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->addColumn('action', function($row){
                    return '<a href="/transaction/edit/'.$row->id.'">Edit</a>'; // Example action
                })
                ->make(true);
        }

        return view('transaction.transaction_detail', ['date' => $date]);
    }      

    public function monthlySummaryApi(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
    
        $transactions = Transaction::selectRaw("
                DATE_FORMAT(transactions.created_at, '%Y-%m-%d') as date,
                SUM(case when type = 'income' then amount else 0 end) as total_income,
                SUM(case when type = 'expense' then amount else 0 end) as total_expense
            ")
            ->whereYear('transactions.created_at', $year)
            ->whereMonth('transactions.created_at', $month)
            ->where('user_id', auth()->id())
            ->groupBy('date')
            ->get();
    
        return response()->json($transactions);
    }

    public function dailyDetailApi(Request $request)
    {
        $date = $request->date;
        $transactions = Transaction::select(
            'name as title',
            'transactions.created_at as start',
            'amount',
            'type',
            'tags.tagname'
        )
        ->leftJoin('tags', 'transactions.tag_id', '=', 'tags.id')
        ->where('user_id', auth()->id())
        ->whereDate('transactions.created_at', $date)
        ->get();

        return response()->json($transactions);
    }    
}
