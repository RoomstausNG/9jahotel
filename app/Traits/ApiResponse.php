<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        // Check if the data is a resource collection with pagination
        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection && method_exists($data, 'resource') && $data->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response['meta'] = $data->resource->toArray();
            unset($response['meta']['data']); // Remove the data key from meta
            $response['links'] = $data->resource->toArray()['links'];
        } else if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection && method_exists($data, 'response') && method_exists($data->response(), 'getData')) {
             $responseData = $data->response()->getData(true);
             if (isset($responseData['meta'])) {
                 $response['meta'] = $responseData['meta'];
             }
             if (isset($responseData['links'])) {
                 $response['links'] = $responseData['links'];
             }
        }

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}