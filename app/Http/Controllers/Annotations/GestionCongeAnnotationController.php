<?php

namespace App\Http\Controllers\Annotations ;

/**
 * @OA\Security(
 *     security={
 *         "BearerAuth": {}
 *     }),

 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"),

 * @OA\Info(
 *     title="Your API Title",
 *     description="Your API Description",
 *     version="1.0.0"),

 * @OA\Consumes({
 *     "multipart/form-data"
 * }),

 *

 * @OA\POST(
 *     path="/api/conges",
 *     summary="mes pointages",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="201", description="Created successfully"),
 * @OA\Response(response="400", description="Bad Request"),
 * @OA\Response(response="401", description="Unauthorized"),
 * @OA\Response(response="403", description="Forbidden"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="motif", type="string"),
 *                     @OA\Property(property="date_fin", type="string"),
 *                     @OA\Property(property="type", type="string"),
 *                     @OA\Property(property="date_debut", type="string"),
 *                 },
 *             ),
 *         ),
 *     ),
 *     tags={"Gestion Conge"},
*),


 * @OA\GET(
 *     path="/api/conges",
 *     summary="Approuver ou rejeter un permission",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="200", description="OK"),
 * @OA\Response(response="404", description="Not Found"),
 * @OA\Response(response="500", description="Internal Server Error"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="motif", type="string"),
 *                     @OA\Property(property="date_fin", type="string"),
 *                     @OA\Property(property="type", type="string"),
 *                     @OA\Property(property="date_debut", type="string"),
 *                 },
 *             ),
 *         ),
 *     ),
 *     tags={"Gestion Conge"},
*),


*/

 class GestionCongeAnnotationController {}
