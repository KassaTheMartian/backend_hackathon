<?php

namespace App;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Portfolio API",
 *   description="Backend API for the portfolio project."
 * )
 *
 * @OA\Server(
 *   url="{scheme}://{host}",
 *   description="Dynamic server",
 *   @OA\ServerVariable(serverVariable="scheme", default="http", enum={"http","https"}),
 *   @OA\ServerVariable(serverVariable="host", default="localhost:8000")
 * )
 *
 * @OA\Tag(name="Demos", description="Demo resources")
 * @OA\Tag(name="Users", description="User resources")
 * @OA\Tag(name="Health", description="Health checks and diagnostics")
 * @OA\Tag(name="Auth", description="Authentication and authorization")
 * @OA\Tag(name="Bookings", description="Booking management")
 * @OA\Tag(name="Branches", description="Branch management")
 * @OA\Tag(name="Chatbot", description="Chatbot interactions")
 * @OA\Tag(name="Contacts", description="Contact management")
 * @OA\Tag(name="Payments", description="Payment processing")
 * @OA\Tag(name="Posts", description="Post management")
 * @OA\Tag(name="Profile", description="User profile management")
 * @OA\Tag(name="Reviews", description="Review management")
 * @OA\Tag(name="Services", description="Service management")
 * @OA\Tag(name="Staff", description="Staff directory and profiles")
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization",
 *   description="Send as: Bearer <token>"
 * )
 *
 * @OA\Parameter(
 *   parameter="AcceptLanguage",
 *   name="Accept-Language",
 *   in="header",
 *   required=false,
 *   description="Preferred language for localized fields (vi,en,ja,zh)",
 *   @OA\Schema(type="string", enum={"vi","en","ja","zh"})
 * )
 *
 * @OA\Schema(
 *   schema="ApiError",
 *   type="object",
 *   @OA\Property(property="type", type="string", example="ValidationError"),
 *   @OA\Property(property="code", type="string", example="VALIDATION_FAILED"),
 *   @OA\Property(property="details", type="object", additionalProperties=@OA\Schema(type="array", @OA\Items(type="string")))
 * )
 *
 * @OA\Schema(
 *   schema="ApiMeta",
 *   type="object",
 *   @OA\Property(property="page", type="integer", example=1),
 *   @OA\Property(property="page_size", type="integer", example=15),
 *   @OA\Property(property="total_count", type="integer", example=120),
 *   @OA\Property(property="total_pages", type="integer", example=8),
 *   @OA\Property(property="has_next_page", type="boolean", example=true),
 *   @OA\Property(property="has_previous_page", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *   schema="ApiEnvelope",
 *   type="object",
 *   @OA\Property(property="success", type="boolean", example=true),
 *   @OA\Property(property="message", type="string", example="OK"),
 *   @OA\Property(property="data"),
 *   @OA\Property(property="error", ref="#/components/schemas/ApiError"),
 *   @OA\Property(property="meta", ref="#/components/schemas/ApiMeta"),
 *   @OA\Property(property="trace_id", type="string", example="c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e"),
 *   @OA\Property(property="timestamp", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="Demo",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="title", type="string", example="My demo"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="is_active", type="boolean", example=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Jane Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
 *   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedDemo",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *     @OA\Schema(
 *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Demo"))
 *     )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedUser",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *     @OA\Schema(
 *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
 *     )
 *   }
 * )
 *
 * @OA\Response(
 *   response="ValidationError",
 *   description="Validation failed",
 *   @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
 * )
 * @OA\Response(
 *   response="NotFound",
 *   description="Resource not found",
 *   @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
 * )
 * @OA\Response(
 *   response="Unauthorized",
 *   description="Unauthorized",
 *   @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
 * )
 * @OA\Response(
 *   response="Forbidden",
 *   description="Forbidden",
 *   @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
 * )
 */
class OpenApi
{
    // This class holds only OpenAPI annotations.
}


