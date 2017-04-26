<?php  

/**
 *	Rest Class

 */

 class Rest {  
   public $tipo = "application/json";  
   public $datosURL = array();
   public $datosJSON;
   public $metodo;  
   private $_codEstado = 200;  
   public function __construct() {  
     $this->tratarEntrada();  
   }  
   public function mostrarRespuesta($data, $estado) {  
     $this->_codEstado = ($estado) ? $estado : 200;//si no se envía $estado por defecto será 200  
     $this->setCabecera();  
     echo '<br>'.$data;  
     exit;  
   }  
   private function setCabecera() {  
     header("HTTP/1.1 " . $this->_codEstado . " " . $this->getCodEstado());  
     header("Content-Type:" . $this->tipo . ';charset=utf-8');  
   }  
   
   private function descomponerURL($data){
	   $entrada=explode('/',rtrim($data,'/'));
	   return $entrada;   
   }
   
   private function obtenerEntradaJSON(){
	   $post=null;
	   $ph = fopen("php://input", "rb");
	   while (!feof($ph)){
		   $post .= fread($ph, 4096);
	   }
	   fclose($ph);
	   $params = json_decode($post, true);
	   return $params;
   }
    
   private function tratarEntrada() {
	 $getService = array_keys($_GET);
	 $service = $getService[0]; 
     $this->metodo= $_SERVER['REQUEST_METHOD'];
      
     switch ($this->metodo) {  
       case "GET":   
         $this->datosURL = $this->descomponerURL($service); 
         break;  
       
       case "POST":  
         $this->datosURL = $this->descomponerURL($service); 
         $this->datosJSON = $this->obtenerEntradaJSON(); 
         break;  
       
       case "DELETE":
         $this->datosURL = $this->descomponerURL($service); 
         break;
       
       case "PUT":  
         $this->datosURL = $this->descomponerURL($service); 
         $this->datosJSON = $this->obtenerEntradaJSON(); 
         break;    
       
       default:  
		 $this->datosURL = $this->descomponerURL($service);
         http_response_code(404);  
         break; 
     }  
   }  
   private function getCodEstado() {  
     $estado = array(  
       200 => 'OK',  //
       201 => 'Created',  
       202 => 'Accepted',  
       204 => 'No Content',  
       301 => 'Moved Permanently',  
       302 => 'Found',  
       303 => 'See Other',  
       304 => 'Not Modified',  
       400 => 'Bad Request', // 
       401 => 'Unauthorized',  
       403 => 'Forbidden',  
       404 => 'Not Found',  
       405 => 'Method Not Allowed',
       409 => 'Conflict',  //
       500 => 'Internal Server Error');  
     $respuesta = ($estado[$this->_codEstado]) ? $estado[$this->_codEstado] : $estado[500];  
     return $respuesta;  
   }  
 }  
 ?>  
