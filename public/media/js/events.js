
window.onload = function(){

    var toggleFlag = function(position){

        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("action", '/');

        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", 'flag');
        hiddenField.setAttribute("value", position);

        form.appendChild(hiddenField);


        document.body.appendChild(form);
        form.submit();

    };

    var i;

    var squares = document.querySelectorAll('.minesweeper-square:not(.revealed)');
    for (i = 0 ; i < squares.length; i ++){

        squares.item(i).addEventListener('contextmenu', function(ev) {
            ev.preventDefault();

            toggleFlag(this.value);

            return false;
        }, false);

    }

    var flags = document.getElementsByClassName('minesweeper-flag');
    for (i = 0 ; i < flags.length; i ++) {

        flags.item(i).addEventListener('contextmenu', function (ev) {
            ev.preventDefault();

            toggleFlag(ev.target.parentElement.value);

            return false;
        }, false);

    }



};