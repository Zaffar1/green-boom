<?php

use App\Http\Controllers\BenDuffyQuestionController;
use App\Http\Controllers\CatalogBroucherController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\MsdSheetController;
use App\Http\Controllers\PerfectSaleController;
use App\Http\Controllers\PerfectSaleMediaController;
use App\Http\Controllers\ProductAllDataController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDataController;
use App\Http\Controllers\SalesTipController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingMediaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoCategoryController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WelcomeVideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [UserController::class, 'registerUser']);
Route::post('register-old', [UserController::class, 'createUser']);
Route::post('login', [UserController::class, 'login']);
Route::post('login-user', [UserController::class, 'loginUser']);

Route::post('forgot-password', [UserController::class, 'reset']);

Route::group(['middleware' => ['auth:api']], function () {

    Route::group(['prefix' => 'customer'], function () {

        //////// Home api
        Route::post('send-type', [TrainingController::class, 'hitRoute']);

        ///////////// User basic api's
        Route::get('verify', [UserController::class, 'verify']);
        Route::post('update-user', [UserController::class, 'updateUser']);

        ////////// Training
        Route::get('training-list', [TrainingController::class, 'customerTrainingList']);
        Route::post('training-media', [TrainingMediaController::class, 'trainingMedia']);
        Route::get('training-detail', [TrainingController::class, 'trainingDetail']);

        //////////// Videos
        Route::get('all-videos', [VideoController::class, 'customerAllVideos']);
        Route::get('video-detail', [VideoController::class, 'videoDetail']);

        ///////////// MSDS Sheets
        Route::get('msdsSheet-list', [MsdSheetController::class, 'customerAllMsdsSheets']);
        Route::get('msdsSheet-detail', [MsdSheetController::class, 'msdSheetDetail']);

        /////////// Catalog and Brouchers
        Route::get('catalogBrouchers-list', [CatalogBroucherController::class, 'customerAllCatalogs']);

        //////////// Product
        Route::get('product-list', [ProductController::class, 'allProducts']);
        Route::get('product-detail', [ProductController::class, 'productDetail']);
        Route::get('all-product-list', [ProductController::class, 'customerAllProducts']);
        // Route::get('product-data/{id}', [ProductController::class, 'productData']);

        Route::get('product-data/{id}', [ProductAllDataController::class, 'allProductData']);
        //////////// Product Data
        // Route::get('product-data-list', [ProductDataController::class, 'customerAllProductData']);

        /////// Faqs
        Route::get('faqs-list', [FaqController::class, 'customerAllFaqs']);

        ////// BenDuffy Questions
        Route::get('all-benDuffy', [BenDuffyQuestionController::class, 'customerAllBenDuffy']);

        ////////// Perfect Sales Pitch
        Route::get('all-perfectSale', [PerfectSaleController::class, 'customerAllPerfectSales']);
    });

    Route::group(['prefix' => 'admin'], function () {
        /////////////Routes For Admin

        Route::get('verify', [UserController::class, 'verify']);
        Route::post('update-user', [UserController::class, 'updateUser']);

        Route::get('users', [UserController::class, 'users']);
        Route::post('block-user/{id}', [UserController::class, 'deActive']);
        Route::post('user-status/{id}', [UserController::class, 'status']);
        Route::get('user-detail/{id}', [UserController::class, 'userDetail']);

        //////// Welcome Video
        Route::get('all-welcome-videos', [WelcomeVideoController::class, 'allWelcomeVideos']);
        Route::post('add-welcome-video', [WelcomeVideoController::class, 'uploadWelcomeVideo']);
        Route::post('update-welcome-video', [WelcomeVideoController::class, 'updateWelcomeVideo']);
        Route::delete('delete-welcome-video/{id}', [WelcomeVideoController::class, 'deleteWelcomeVideo']);
        Route::post('change-status/{id}', [WelcomeVideoController::class, 'videoStatus']);

        /////////// Training 
        Route::get('all-training', [TrainingController::class, 'allTraining']);
        Route::post('add-training', [TrainingController::class, 'addTraining']);
        Route::post('update-training', [TrainingController::class, 'updateTraining']);
        Route::delete('delete-training/{id}', [TrainingController::class, 'deleteTraining']);
        Route::post('training-status/{id}', [TrainingController::class, 'TrainingStatus']);

        /////// Training Media
        Route::post('add-training-media', [TrainingMediaController::class, 'addTrainingMedia']);
        Route::get('training-media/{id}', [TrainingMediaController::class, 'trainingMedia']);
        Route::post('update-training-media', [TrainingMediaController::class, 'updateMedia']);
        Route::delete('delete-training-media/{id}', [TrainingMediaController::class, 'deleteTrainingMedia']);
        Route::post('training-media-status/{id}', [TrainingMediaController::class, 'TrainingMediaStatus']);

        /////////// Video Categories

        Route::get('video-categories', [VideoCategoryController::class, 'allVideoCategories']);
        Route::post('add-category', [VideoCategoryController::class, 'addVideoCat']);
        Route::post('update-category', [VideoCategoryController::class, 'updateVideoCat']);
        Route::delete('delete-category/{id}', [VideoCategoryController::class, 'deleteVideoCategory']);
        Route::post('video-cat-status/{id}', [VideoCategoryController::class, 'videoCatStatus']);
        Route::get('video-cat-videos/{id}', [VideoCategoryController::class, 'videoCatVideos']);

        /////////// Videos
        Route::get('all-videos', [VideoController::class, 'allVideos']);
        Route::post('add-video', [VideoController::class, 'addVideo']);
        Route::post('update-video', [VideoController::class, 'updateVideo']);
        Route::delete('delete-video/{id}', [VideoController::class, 'deleteVideo']);
        Route::get('video-detail', [VideoController::class, 'videoDetail']);
        Route::post('video-status/{id}', [VideoController::class, 'videoStatus']);

        /////////// MSDS SHEETS
        Route::get('all-sheets', [MsdSheetController::class, 'allMsdsSheets']);
        Route::post('add-sheet', [MsdSheetController::class, 'addMsdSheet']);
        Route::post('update-sheet', [MsdSheetController::class, 'updateMsdSheet']);
        Route::delete('delete-sheet/{id}', [MsdSheetController::class, 'deleteMsdSheet']);
        Route::get('msdsSheet-detail', [MsdSheetController::class, 'msdSheetDetail']);
        Route::post('msd-sheet-status/{id}', [MsdSheetController::class, 'msdStatus']);

        ////////// Perfect Sales Pitch
        Route::get('all-perfect-sale', [PerfectSaleController::class, 'allPerfectSales']);
        Route::post('add-perfect-sale', [PerfectSaleController::class, 'addPerfectSale']);
        Route::post('update-perfect-sale', [PerfectSaleController::class, 'updatePerfectSale']);
        Route::delete('delete-perfectSale/{id}', [PerfectSaleController::class, 'deletePerfectSale']);
        Route::post('perfectSale-status/{id}', [PerfectSaleController::class, 'PerfectSaleStatus']);
        Route::get('perfect-sale-media/{id}', [PerfectSaleController::class, 'perfectSaleMedia']);

        ////////// Perfect Sale Media
        Route::post('add-perfect-sale-media', [PerfectSaleMediaController::class, 'addPerfectSaleMedia']);
        Route::post('update-perfect-sale-media', [PerfectSaleMediaController::class, 'updatePerfectSaleMedia']);
        Route::delete('delete-perfectSaleMedia/{id}', [PerfectSaleMediaController::class, 'deletePerfectSaleMedia']);
        Route::post('perfectSaleMedia-status/{id}', [PerfectSaleMediaController::class, 'PerfectSaleMediaStatus']);
        Route::post('add-script', [PerfectSaleMediaController::class, 'addPerfectSaleMediaScript']);

        ////////////// Script Data
        Route::get('script-media/{id}', [PerfectSaleMediaController::class, 'scriptMedia']);
        Route::post('add-script-data', [ScriptController::class, 'addScriptData']);
        Route::post('update-script-data', [ScriptController::class, 'updateScriptData']);
        Route::delete('delete-scriptData/{id}', [ScriptController::class, 'deleteScriptData']);
        Route::post('scriptData-status/{id}', [ScriptController::class, 'ScriptDataStatus']);

        ////////// SalesTips
        Route::get('all-sales-tips', [SalesTipController::class, 'allSalesTips']);
        Route::post('add-sales-tip', [SalesTipController::class, 'addSalesTip']);
        Route::post('update-sales-tip', [SalesTipController::class, 'updateSalesTip']);
        Route::delete('delete-sales-tip/{id}', [SalesTipController::class, 'deleteSalesTip']);
        Route::post('sales-tip-status/{id}', [SalesTipController::class, 'salesTipStatus']);


        /////////// Catalog & Brouchers
        Route::get('all-brouchers', [CatalogBroucherController::class, 'allCatalogs']);
        Route::post('add-catalog', [CatalogBroucherController::class, 'addCatalogBroucher']);
        Route::post('update-catalog', [CatalogBroucherController::class, 'updateCatalogBroucher']);
        Route::delete('delete-catalog/{id}', [CatalogBroucherController::class, 'deletecatalogBroucher']);
        Route::get('catalog-detail', [CatalogBroucherController::class, 'CatalogDetail']);
        Route::post('catalog-status/{id}', [CatalogBroucherController::class, 'catalogStatus']);

        /////////// Products
        Route::get('all-products', [ProductController::class, 'allProducts']);
        Route::post('add-product', [ProductController::class, 'addProduct']);
        Route::post('update-product', [ProductController::class, 'updateProduct']);
        Route::delete('delete-product/{id}', [ProductController::class, 'deleteProduct']);
        Route::get('product-detail', [ProductController::class, 'productDetail']);
        Route::post('product-status/{id}', [ProductController::class, 'productStatus']);

        /////////////// Product Data
        Route::get('all-product-data', [ProductDataController::class, 'allProductData']);
        // Route::post('add-product-data', [ProductDataController::class, 'addProductData']);
        Route::get('productData/{id}', [ProductDataController::class, 'productData']);
        Route::delete('delete-product-data/{id}', [ProductDataController::class, 'deleteProductData']);
        Route::post('add-product-data', [ProductAllDataController::class, 'addProductData']);

        /////////// Youtube
        Route::get('videos', [VideoController::class, 'youTubeVideos']);

        //////////// Faqs
        Route::get('all-faqs', [FaqController::class, 'allFaqs']);
        Route::post('add-faq', [FaqController::class, 'addFaq']);
        Route::post('update-faq', [FaqController::class, 'updateFaq']);
        Route::delete('delete-faq/{id}', [FaqController::class, 'deleteFaq']);

        /////////// BenDuffyQuestion
        Route::get('all-benDuffy', [BenDuffyQuestionController::class, 'allBenDuffy']);
        Route::post('add-benDuffy', [BenDuffyQuestionController::class, 'addBenDuffy']);
        Route::post('update-benDuffy', [BenDuffyQuestionController::class, 'updateBenDuffy']);
        Route::delete('delete-benDuffy/{id}', [BenDuffyQuestionController::class, 'deleteBenDuffy']);
    });
});

Route::get('user-detail/{id}', [UserController::class, 'userDetail']);
