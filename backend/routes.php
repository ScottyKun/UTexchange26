<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {

    //auth
    $r->post('/api/auth/login',    ['ApiAuth', 'login']);
    $r->post('/api/auth/register', ['ApiAuth', 'register']);
    $r->post('/api/auth/logout',   ['ApiAuth', 'logout']);  
    $r->get ('/api/auth/me',       ['ApiAuth', 'me']);     

    //users
    $r->get('/api/users', ['ApiUsers', 'index']);
    $r->get('/api/users/{id:\d+}', ['ApiUsers', 'show']);
    $r->post('/api/users/create', ['ApiUsers', 'add']);
    $r->put('/api/users/update/{id:\d+}', ['ApiUsers', 'update']);
    $r->delete('/api/users/delete/{id:\d+}', ['ApiUsers', 'destroy']);
    $r->put('/api/users/{id:\d+}/password', ['ApiUsers', 'updatePassword']);
    $r->put('/api/users/{id:\d+}/activate', ['ApiUsers', 'activate']);
    $r->put('/api/users/{id:\d+}/deactivate', ['ApiUsers', 'deactivate']);

 
    //categories
    $r->get('/api/categories', ['ApiCategories', 'index']);
    $r->get('/api/categories/{id:\d+}', ['ApiCategories', 'show']);
    $r->post('/api/categories/add', ['ApiCategories', 'store']);
    $r->put('/api/categories/update/{id:\d+}', ['ApiCategories', 'update']);
    $r->delete('/api/categories/delete/{id:\d+}', ['ApiCategories', 'delete']);
    $r->put('/api/categories/{id:\d+}/activate', ['ApiCategories', 'activate']);
    $r->put('/api/categories/{id:\d+}/deactivate', ['ApiCategories', 'deactivate']);


    //annonces
    $r->get('/api/annonces', ['ApiAnnonces', 'index']);
    $r->get('/api/annonces/mine', ['ApiAnnonces', 'mine']); 
    $r->get('/api/annonces/user/{id:\d+}', ['ApiAnnonces', 'getByUserId']);        
    $r->get('/api/annonces/{id:\d+}', ['ApiAnnonces', 'show']);
    $r->post('/api/annonces/add', ['ApiAnnonces', 'store']);
    $r->put('/api/annonces/update/{id:\d+}', ['ApiAnnonces', 'update']);
    $r->delete('/api/annonces/delete/{id:\d+}', ['ApiAnnonces', 'delete']);
    $r->patch('/api/annonces/{id:\d+}/type', ['ApiAnnonces', 'updateType']);
    $r->patch('/api/annonces/{id:\d+}/status', ['ApiAnnonces', 'updateStatus']);
    $r->post('/api/annonces/{id:\d+}/photos', ['ApiAnnonces', 'uploadPhotos']);
    

    //favoris
    $r->post('/api/annonces/{id:\d+}/favori', ['ApiFavoris',  'toggle']);
    $r->get('/api/annonces/{id:\d+}/is-favori', ['ApiFavoris',  'check']);
    $r->get('/api/favoris', ['ApiFavoris', 'index']);


    //photos
    $r->get('/api/photos/{annonceId:\d+}/{fichier}', ['ApiAnnonces', 'getPhotos']);
    $r->delete('/api/photos/{id:\d+}', ['ApiAnnonces', 'deletePhoto']);
    $r->put('/api/photos/{id:\d+}/cover', ['ApiAnnonces', 'setCover']);


    //conversations
    $r->get('/api/conversations', ['ApiConversations', 'index']);
    $r->post('/api/conversations/start', ['ApiConversations', 'store']);
    $r->get('/api/conversations/{id}', ['ApiConversations', 'show']);
    $r->put('/api/conversations/{id}/terminate', ['ApiConversations', 'terminate']);
    $r->delete('/api/conversations/{id}', ['ApiConversations', 'delete']);


    //messages
    $r->get('/api/messages/unread-count',   ['ApiMessages', 'unreadCount']);
    $r->delete('/api/messages/{id}/delete', ['ApiMessages', 'deleteMessage']);
    $r->put('/api/messages/{id}/update', ['ApiMessages', 'updateMessage']);
    $r->get('/api/conversations/{id}/messages', ['ApiMessages', 'getMessages']);
    $r->post('/api/conversations/{id}/messages/send', ['ApiMessages', 'sendMessage']);
    $r->post('/api/conversations/{id}/read', ['ApiMessages', 'markAsRead']);


    //avis
    $r->post('/api/conversations/{convId}/avis', ['ApiAvis', 'store']);
    $r->delete('/api/conversations/{convId}/avis/{avisId}', ['ApiAvis', 'destroy']);
    $r->put('/api/conversations/{convId}/avis/{avisId}', ['ApiAvis', 'update']);
    $r->get('/api/users/{id:\d+}/avis', ['ApiAvis',  'byUser']);


    //admin
    $r->get('/api/admin/annonces', ['ApiAdmin', 'allAnnonces']);
    $r->get('/api/admin/avis', ['ApiAdmin', 'allAvis']);
    $r->put('/api/admin/conversations/{convId}/avis/{avisId}/activate', ['ApiAdmin', 'activateAvis']);
    $r->put('/api/admin/conversations/{convId}/avis/{avisId}/deactivate', ['ApiAdmin', 'deactivateAvis']);
    $r->put('/api/admin/annonces/{id:\d+}/report', ['ApiAdmin', 'reportAnnonce']);
    
    
    
    //PowerBI
    $r->get('/api/adminBI/stats', ['ApiAdmin', 'stats']);
    $r->get('/api/adminBI/logs/activity', ['ApiAdmin', 'activityLogs']);
    $r->get('/api/adminBI/logs/connections', ['ApiAdmin', 'connectionLogs']);
    $r->get('/api/adminBI/annonces', ['ApiAdmin', 'allAnnoncesBI']);
    $r->get('/api/adminBI/avis', ['ApiAdmin', 'allAvisBI']);
    $r->get('/api/adminBI/categories', ['ApiCategories', 'indexBI']);
    $r->get('/api/adminBI/users', ['ApiUsers', 'indexBI']);
    $r->post('/auth/bi/token', ['ApiAuth', 'generateBiToken']);

};