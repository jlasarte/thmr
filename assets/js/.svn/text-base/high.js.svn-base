function cargar_twits(usuario, numero_de_twits, insertar_en ){
	// armamos el array con los datos para enviar a la api de twiter
	datos = {
				screen_name: usuario, //nombre de usuario
				include_rts: true, // incluir retweet?
				count: numero_de_twits, //numero de tweets
				include_entities: false, //incluir entidades?

			};
	
	$.ajax({
			url: 'http://api.twitter.com/1/statuses/user_timeline.json/', //url para la solicitud
			type: 'GET', // tipo de request [POST o GET]
			dataType: 'jsonp', // el tipo de datos que queremos recibir
			data: datos, // los datos para enviar con el request

			// la funcion sucess se ejecuta una vez que se completo el pedido
			// y solo si se completo satisfactoriamente
			success: function(data, textStatus, xhr) {
				//eliminamos el div loading
				$("#loading").remove();
				 // insertamos los tweets
				 for (var i = 0; i < data.length; i++) {
                    var html = '<div class="tweet">'+ify.clean(data[i].text)+'<div class="time">'+(data[i].created_at)+'</div>';
					$(insertar_en).append(html);
				 }				
			},

			//la funcion beforeSend se ejecuta antes de enviar la solicitud
			beforeSend : function(){
				$(insertar_en).html('<div id="loading"><img src="loading.gif"/></div>');
			}	

	});
}

function pygmentize(anchor) {
  var code = anchor.text();
  var lang = anchor.attr("title");
  var data = {code: code, lexer:lang, style: 'monokai'}

  $.ajax({
      url: 'http://hilite.me/api', //url para la solicitud
      type: 'GET', // tipo de request [POST o GET]
      data: data, // los datos para enviar con el request

      // la funcion sucess se ejecuta una vez que se completo el pedido
      // y solo si se completo satisfactoriamente
      success: function(data, textStatus, xhr) {
        //eliminamos el div loading
        var html = $(data.responseText);
        console.log(html);
        var code = html.find("pre");
        code = '<pre class="highlighted">'+code.html()+'</pre>';
        $(".pygments").replaceWith(code);
      },

      //la funcion beforeSend se ejecuta antes de enviar la solicitud
      beforeSend : function(){
        $(".pygments").html('<div id="loading"><img src="loading.gif"/></div>');
      } 

  });
}

$(document).ready(function () {
// hacemos el bind del evento click en cargar_twits a la funcion que obtiene los twits
    $(".pygments").click(
        function() {
          var anchor = $(this);
          pygmentize(anchor);
          }
    )
});