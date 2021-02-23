<?php
namespace coreapi\Utilities\ConnectionManagers;


interface BaseConnectionManager
{
    public function requestGet($url, $data, $headers = []);

    public function requestPost($url, $data, $headers = []);

    public function requestDelete($url, $headers = []);

    public function requestPut($url, $data, $headers = []);

}