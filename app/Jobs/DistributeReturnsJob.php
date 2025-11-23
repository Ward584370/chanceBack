<?php

namespace App\Jobs;

use App\Models\investment_opprtunities;
use App\Http\Controllers\ReturnsController;
use App\Models\returns;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class DistributeReturnsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // جلب جميع الفرص مع المصنع فقط (بدون User مسبقاً)
        $opportunities = investment_opprtunities::with('factory')->get();

        $controller = new ReturnsController();

        foreach ($opportunities as $opportunity) {

            // جلب المالك مباشرة من قاعدة البيانات لضمان الحفظ
            $owner = User::find($opportunity->factory->user_id);

            // جلب آخر تاريخ توزيع أرباح، أو تاريخ إنشاء الفرصة إذا لم يتم توزيع شيء
            $lastReturn = returns::where('opprtunty_id', $opportunity->id)
                                ->orderBy('return_date', 'desc')
                                ->first();

            $lastReturnDate = $lastReturn ? Carbon::parse($lastReturn->return_date) : Carbon::parse($opportunity->created_at);

            // حساب تاريخ التوزيع التالي بناء على Payout Frequency (بالشهور)
            $nextPayoutDate = $lastReturnDate->copy()->addMonths($opportunity->payout_frequency);

            // إذا لم يحن موعد التوزيع بعد، تخطى هذه الفرصة
            if (Carbon::now()->lt($nextPayoutDate)) {
                continue;
            }

            // حساب مبلغ الأرباح
            $returnAmount = $opportunity->collected_amount * ($opportunity->profit_percentage / 100);

            // تحقق من رصيد المالك
            if ($owner->wallet < $returnAmount) {
                $owner->is_suspended = 1; // تعليق الحساب
                $owner->save();
                continue; // تخطي هذه الفرصة
            }

            // توزيع الأرباح مباشرة مع تمرير المالك
            $request = request()->merge(['amount' => $returnAmount]);
            $controller->distributeReturn($request, $opportunity->id, $owner);
        }
    }
}