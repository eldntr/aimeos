<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;

/**
 * Class ReviewModerationController
 *
 * Handles review moderation controller operations for the application.
 */
class ReviewModerationController extends Controller
{
    /**
     * Get Aimeos context with locale bootstrap
     */
    private function getContext()
    {
        $context = app('aimeos.context')->get(false);
        $localeManager = MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        $context->setLocale($localeItem);
        return $context;
    }

    /**
     * Fetch all product reviews for moderation
     */
    public function index()
    {
        $context = $this->getContext();
        $reviewManager = MShop::create($context, 'review');
        
        $filter = $reviewManager->filter();
        $filter->add($filter->compare('==', 'review.domain', 'product'));
        
        $reviews = $reviewManager->search($filter);
        
        $list = [];
        foreach ($reviews as $review) {
            $list[] = [
                'id' => $review->getId(),
                'product_id' => $review->getRefId(),
                'reviewer_name' => $review->getName() ?: 'Anonim',
                'rating' => (int) $review->getRating(),
                'comment' => $review->getComment(),
                'status' => $review->getStatus(), // 1 = Visible, 0 = Hidden/Banned
                'created_at' => $review->getTimeCreated()
            ];
        }

        return response()->json([
            'message' => 'Daftar ulasan berhasil diambil.',
            'data' => $list
        ]);
    }

    /**
     * Toggle visibility status of a review (Hide/Unhide)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $context = $this->getContext();
        $reviewManager = MShop::create($context, 'review');

        try {
            $review = $reviewManager->get($id);
            
            $reviewManager->begin();
            $review->setStatus((int) $request->status);
            $reviewManager->save($review);
            $reviewManager->commit();

            $statusText = $review->getStatus() === 1 ? 'ditampilkan kembali' : 'disembunyikan (Banned)';

            return response()->json([
                'message' => "Ulasan berhasil $statusText.",
                'data' => [
                    'id' => $review->getId(),
                    'status' => $review->getStatus()
                ]
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Ulasan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $reviewManager->rollback();
            return response()->json(['message' => 'Gagal mengubah status ulasan.', 'error' => $e->getMessage()], 500);
        }
    }
}
