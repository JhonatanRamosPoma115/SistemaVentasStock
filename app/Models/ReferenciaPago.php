<?php
namespace App\Models;
class ReferenciaPago extends \MongoDB\Laravel\Eloquent\Model
{
    protected $connection='mongodb';
    protected $table='referencias_pago';
    protected $guarded=[];
}
