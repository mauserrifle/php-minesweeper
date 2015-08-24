
window.onload = function(){

    var squares = document.querySelectorAll('.minesweeper-square:not(.revealed)');

    for (var i = 0 ; i < squares.length; i ++){

        squares.item(i).addEventListener('contextmenu', function(ev) {
            ev.preventDefault();

            var form = document.createElement("form");
            form.setAttribute("method", 'post');
            form.setAttribute("action", '/');

            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", 'flag');
            hiddenField.setAttribute("value", this.value);

            form.appendChild(hiddenField);


            document.body.appendChild(form);
            form.submit();

            return false;
        }, false);

    }



};