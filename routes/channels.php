<?php

############ Start Chats ############
Broadcast::channel('Room.Chat.{id}', function ($user, $id) {
//    return true;
    return (int) $user->id === (int) $id;
});


//Broadcast::channel('Read.Messages.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});
############ End Chats ############



//Notifications
Broadcast::channel('User.Notify.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

