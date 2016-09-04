var listNumber=0,
    agregarPapers= document.getElementById("agregarPapers"),
    btnPaper = document.getElementById("btnPaper"),
    contadorPapers = 0;

var state = new Array("Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antarctica", "Antigua and Barbuda",
"Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados",
"Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana",
"Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde",
"Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic",
"Congo, Republic of the", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark",
"Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea",
"Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana",
"Greece", "Greenland", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong",
"Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan",
"Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia",
"Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar",
"Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia",
"Moldova", "Mongolia", "Morocco", "Monaco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand",
"Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru",
"Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Samoa", "San Marino", " Sao Tome",
"Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia",
"Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden",
"Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago",
"Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States",
"Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");

$(window).load(function() {

    llenarPaises();

    $( "#formInscripcion" ).submit(function( event ) {
       $('.f1-buttons').html('Subiendo archivos.<br>Enviando Formulario.<br>Espere un momento mientras se le redirecciona.');
    });

    $('#categoria').on('change', function() {
        if( this.value.substr(0,10) == 'Estudiante' ){
            document.getElementById('alumno').style.display="";
        }else{
            document.getElementById('alumno').style.display="none";
        }
        //Solo autores pueden inscribir papers
        if( this.value == 'Autor'){
            document.getElementById('soloAutores').style.display="";
            document.getElementById('msjNoAutores').innerHTML="";
            $("#cantPaper").val("0");
        }
        else{
            $("#cantPaper").val("-1");
            document.getElementById('soloAutores').style.display="none";
            document.getElementById('msjNoAutores').innerHTML="<center><h4>No estás en la categoría de autor.<br>Puedes saltarte este paso.</h3></center>";

        }
    });

    $("#f1-ticketVinos").change(function () {
        if($("#f1-ticketVinos").val()>0){
            document.getElementById('vino').style.display="";
        }
        else{
            document.getElementById('vino').style.display="none";
        }
    });

    //VENUE
    $("input[name=tipo]:radio").change(function () {
        if($("input[name=tipo]:checked").val()=='Miembro CLEI, SCCC, IEEE o Co-Org (PUCV, USM)'){
            document.getElementById('membresia').style.display="";
        }
        else{
            document.getElementById('membresia').style.display="none";
        }
    });




    // ARCHIVOS REGISTRO DE CATEGORÍA
    document.getElementById('files0').addEventListener('change', function(){
        listNumber=0;
    });
    document.getElementById('files0').addEventListener('change', handleFileSelect, false);
    document.getElementById('files1').addEventListener('change', function(){
        listNumber='1';
    });
    document.getElementById('files1').addEventListener('change', handleFileSelect, false);

    // COMPROBANTE
    document.getElementById('files2').addEventListener('change', function(){
        listNumber='2';
    });
    document.getElementById('files2').addEventListener('change', handleFileSelect, false);


    //PAPERS





    btnPaper.addEventListener('click',function(){
        contadorPapers++;
        //EDITAR EN CASO DE QUERER MÁS PAPERS
        if(contadorPapers<=2){
            newPapers(contadorPapers);
            //Esto se hace porque, de esta manera, se envia la cantidad de papers al inscribir.php
            $("#cantPaper").val(contadorPapers);
        }
        if(contadorPapers==2) btnPaper.style.display='none';
    });

});

function llenarPaises(){
    for(i=0;i<state.length;i++){
        sel=false;
        if(state[i]=='Peru') sel=true;
        $('#pais').append($('<option>', {
            value: state[i],
            text: state[i],
            selected: sel
        }));
    }
}

function newPapers(conta){
    if(conta==1) $("#agregarPapers").append('<h4><strong>Papers Adicionales (max. 2)</strong></h4>');
    text='<h4>'+(conta+1)+'. Paper Asociado al Registro:</h4>                                <h5>*'+(conta+1)+'.1 Id Artículo (JEMS o Easychair)</h5>                                <div class="form-group">                                    <label class="sr-only" for="f1-id'+conta+'">Id</label>                                    <input type="text" name="f1-id'+conta+'" placeholder="Id..." class="f1-id'+conta+' form-control" id="f1-id'+conta+'">                                </div>                                <h5>*'+(conta+1)+'.2 Título del artículo</h5>                                <div class="form-group">                                    <label class="sr-only" for="f1-titulo'+conta+'">Título</label>                                    <input type="text" name="f1-titulo'+conta+'" placeholder="Título..." class="f1-titulo'+conta+' form-control" id="f1-titulo'+conta+'">                                </div>                                <h5>*'+(conta+1)+'.3 Evento CLEI o SCCC donde se presenta</h5>                                <div class="form-group">                                   <label class="sr-only" for="f1-evento'+conta+'">Categoría</label>                                    <select class="f1-evento form-control" name="f1-evento'+conta+'" id="f1-evento'+conta+'">                                        <option value="0">Seleccione una opción</option>                             <option value="IFIP LANC 2016">IFIP LANC 2016</option>                                        <option value="LAT.AM.SYMP. ON COMPUTER GRAPHICS, VIRTUAL REALITY, AND IMAGE PROCESSING">LAT.AM.SYMP. ON COMPUTER GRAPHICS, VIRTUAL REALITY, AND IMAGE PROCESSING</option>                                        <option value="LAT.AM.SYMP. ON COMPUTING AND SOCIETY">LAT.AM.SYMP. ON COMPUTING AND SOCIETY</option>                                        <option value="LAT.AM.SYMP. ON INFRASTRUCTURE, HARDWARE AND SOFTWARE">LAT.AM.SYMP. ON INFRASTRUCTURE, HARDWARE AND SOFTWARE</option>                                        <option value="LAT.AM.SYMP. ON SOFTWARE ENGINEERING">LAT.AM.SYMP. ON SOFTWARE ENGINEERING</option>                                        <option value="LAT.AM.SYMP. ON OPERATION RESEARCH & ARTIFICIAL INTELLIGENCE">LAT.AM.SYMP. ON OPERATION RESEARCH & ARTIFICIAL INTELLIGENCE</option>                                        <option value="LAT.AM.SYMP. ON LARGE SCALE INFORMATION SYSTEMS">LAT.AM.SYMP. ON LARGE SCALE INFORMATION SYSTEMS</option>                                        <option value="LAT.AM.SYMP. ON DATA MANAGEMENT SYSTEMS">LAT.AM.SYMP. ON DATA MANAGEMENT SYSTEMS</option>                                        <option value="LAT.AM.SYMP. ON THEORY OF COMPUTATION">LAT.AM.SYMP. ON THEORY OF COMPUTATION</option>                                        <option value="XXIV SIMPOSIO EDUCACIÓN SUPERIOR EN COMPUTACIÓN (SIESC)">XXIV SIMPOSIO EDUCACIÓN SUPERIOR EN COMPUTACIÓN (SIESC)</option>                                        <option value="II LATIN AMERICAN CONTEST OF DOCTORAL THESIS (CLTD)">II LATIN AMERICAN CONTEST OF DOCTORAL THESIS (CLTD)</option>                                        <option value="XXIII LATIN AMERICAN CONTEST OF MASTER THESIS (CLTM)">XXIII LATIN AMERICAN CONTEST OF MASTER THESIS (CLTM)</option>                                        <option value="VIII LATIN AMERICAN WOMEN IN COMPUTING CONGRESS(LAWCC)">VIII LATIN AMERICAN WOMEN IN COMPUTING CONGRESS(LAWCC)</option>                                        <option value="VI WORKSHOP IN ACCREDITATION AND NOMENCLATURE OF COMPUTING PROGRAMS (WNAPC)">VI WORKSHOP IN ACCREDITATION AND NOMENCLATURE OF COMPUTING PROGRAMS (WNAPC)</option>                                        <option value="IV SIMPOSIO DE HISTORIA DE LA INFORMÁTICA DE AMÉRICA LATINA Y EL CARIBE (SHIALC)">IV SIMPOSIO DE HISTORIA DE LA INFORMÁTICA DE AMÉRICA LATINA Y EL CARIBE (SHIALC)</option>                                        <option value="9TH LATIN AMERICA NETWORKING CONFERENCE (LANC)">9TH LATIN AMERICA NETWORKING CONFERENCE (LANC)</option>                                        <option value="35TH INTERNATIONAL CONFERENCE OF THE CHILEAN COMPUTER SCIENCE SOCIETY (SCCC 2016)">35TH INTERNATIONAL CONFERENCE OF THE CHILEAN COMPUTER SCIENCE SOCIETY (SCCC 2016)</option>                                    </select>                                </div><hr>';
    $("#agregarPapers").append(text);

}


// MOSTRAR INFORMACIÓN DEL ARCHIVO SUBIDO
function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object

    // files is a FileList of File objects. List some properties.
    var output = [];
    for (var i = 0, f; f = files[i]; i++) {
      output.push('<li><strong>', escape(f.name), '</strong> (', f.type || 'n/a', ') - ',
                  (f.size/1048576).toFixed(1), ' Mb<br>Última modificación: ',
                  f.lastModifiedDate ? f.lastModifiedDate.toLocaleDateString() : 'n/a',
                  '</li>');
    }
    document.getElementById('list'+listNumber).innerHTML = '<ul>' + output.join('') + '</ul>';
}
