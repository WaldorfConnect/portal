<?php

namespace App\Controllers;

use function App\Helpers\getGroupById;

class MapController extends BaseController
{
    public function index(): string
    {
        $targetGroupId = $this->request->getGet('group');
        if (isset($targetGroupId)) {
            $group = getGroupById($targetGroupId);
            if ($group && $group->getLatitude() && $group->getLongitude()) {
                return $this->render('map/MapView', ['targetGroup' => $group]);
            }
        }

        return $this->render('map/MapView');
    }
}