sharedServicesModule.config(function ($httpProvider) {

    var counter = 0;

    $httpProvider.interceptors.push(

        function ($q) {
            return {

                // optional method
                'request': function(config) {
                    // todo start the spinner here
                    $('#spinnerdiv').show();
                    counter++;
                    return $q.when(config);
                },

                // optional method
                'requestError': function(rejection) {
                    // do something on success
                    counter--;
                    if ( counter == 0 ) $('#spinnerdiv').hide();
                    return $q.reject(rejection);
                },

                // optional method
                'response': function(response) {
                    // do something on success
                    counter--;
                    if ( counter == 0 ) $('#spinnerdiv').hide();
                    return $q.when(response);
                },

                'responseError': function(rejection) {
                    // do something on error
                    counter--;
                    if ( counter == 0 ) $('#spinnerdiv').hide();
                    return $q.reject(rejection);
                }

            };
        }

    );
});