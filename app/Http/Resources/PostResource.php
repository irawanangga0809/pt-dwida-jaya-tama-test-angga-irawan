<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    //Properti
    public $status;
    public $message;
    public $resource;
    public $error;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @param  mixed $error (opsional, default null)
     */
    public function __construct($status, $message, $resource, $error=null)
    {
        parent::__construct($resource);
        $this->status  = $status;
        $this->message = $message;
        $this->error = $error;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [  
            'success'   => $this->status,
            'message'   => $this->message,
            'data'      => $this->resource,
            'error'     => $this->error
        ];
    }
}
