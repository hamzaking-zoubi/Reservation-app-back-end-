<?php

use Illuminate\Support\Facades\Broadcast;

//Broadcast::routes(['prefix' => 'api','middleware' => ['auth:userapi']]);
Broadcast::routes(['prefix' => 'api','middleware' => ["api",'auth:userapi']]);


//TEST
Route::match(["get", "post", "delete"],
    "test",[\App\Http\Controllers\Api\Admin\UsersController::class,"CountUserOwnerFacInLastNMonth"]);
//


############### Start Admin ######################

require __DIR__ . "\\Route.Private.Project\\"."Admin\\"."admin.php";

require __DIR__ . "\\Route.Private.Project\\"."Admin\\"."report.php";

############### End Admin ######################

############### Start Users ######################

require __DIR__ . "\\Route.Private.Project\\"."User\\"."authuser.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."user.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."status.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."notifications.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."review.php";

require __DIR__ . "\\Route.Private.Project\\"."User\\"."proposals.php";

############### End Users ######################


############### Start Facilities ######################

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."search_facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."facilities.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."favorite.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."booking.php";

require __DIR__ . "\\Route.Private.Project\\"."Facilities\\"."ownerbooking.php";

############### End Facilities ######################


require __DIR__ . "\\Route.Private.Project\\"."Chat\\"."chats.php";


