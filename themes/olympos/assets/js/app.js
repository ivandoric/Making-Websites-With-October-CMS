import {sayHi, sayBye} from './sayhi';

//sayHi('John');
//sayBye('Sally');

class HelloWorld{
    constructor() {
        alert('Hello World');
    }
}

(function($){

    $('#MoviesFilter').on('change', 'input, select', function(){
        var $form = $(this).closest('form');
        $form.request();
    });

})(jQuery);
