<?php

namespace Mrzlanx532\LaravelBasicComponents\Controllers;

use Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset\BrowserFilterPresetCreateService;
use Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset\BrowserFilterPresetDeleteService;
use Mrzlanx532\LaravelBasicComponents\Service\BrowserFilterPreset\BrowserFilterPresetUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

class BrowserFilterPresetController extends Controller
{
    /**
     * POST
     * browser/preset/create
     * Создание пользовательского пресета
     *
     * @bodyParam title string required
     * @bodyParam browser_id string required
     * @bodyParam filters array required
     *
     * @response {
     *   "id": 1,
     * }
     * @throws ValidationException
     */
    public function create(Request $request, BrowserFilterPresetCreateService $browserFilterPresetCreateService): JsonResponse
    {
        return response()->json(
            $browserFilterPresetCreateService
                ->setParams($request)
                ->handle()
        );
    }

    /**
     * POST
     * browser/preset/update
     * Обновление пользовательского пресета
     *
     * @bodyParam id integer required идетификатор пресета. Example: 1
     * @bodyParam title string required
     *
     * @param Request $request
     * @param BrowserFilterPresetUpdateService $browserFilterPresetUpdateService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, BrowserFilterPresetUpdateService $browserFilterPresetUpdateService): JsonResponse
    {
        return response()->json($browserFilterPresetUpdateService->setParams($request)->handle());
    }

    /**
     * POST
     * browser/preset/delete
     * Удаление пользовательского пресета
     *
     * @bodyParam id int required идетификатор пресета. Example: 1
     *
     * @response {
     *   "status": "success",
     *   "message": "deleted",
     * }
     *
     * @param Request $request
     * @param BrowserFilterPresetDeleteService $browserFilterPresetDeleteService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(Request $request, BrowserFilterPresetDeleteService $browserFilterPresetDeleteService): JsonResponse
    {
        $browserFilterPresetDeleteService->setParams($request)->handle();

        return response()->json(['status' => 'success', 'message' => 'deleted']);
    }
}