<?php
class mysqldb 
{
	
	public static $inst ;
	public static $prefixe ;
	public static $id ;
	public static $base ;
	public static $req ;

	public function __construct($host,$user,$pass,$db, $pref="") {
		self::$prefixe 	= $pref ;
		self::$base 	= $db ;
		//self::$id = mysqli_connect($host, $user, $pass) or die ("Erreur de connection MYSQL");
		try {
			self::$id = new PDO('mysql:dbname='.self::$base.';host=127.0.0.1;charset=UTF8', $user, $pass, array(PDO::ATTR_PERSISTENT => true)) ;
		}
		catch(PDOException $e) {
			die('{"erreur":"connexion mysql a échoué. "'.$e->getMessage().'}') ;
		}
		//mysqli_select_db(self::$id, self::$base); 
		//$this->send("SET NAMES UTF8") ;
	}
	
	public function __destruct() {
		
		$this->close() ;
	}	
	
	public static function unik($query) {
		$query = self::$id->query($query) ;
		if ($t = $query->fetch()) {
			$query->closeCursor();
			if (count($t) > 0) {
				return $t[0] ;
			}
			else return '{"erreur":"erreur dans le contenu du résultat de la requête}' ; ;
		}
		else {
			$query->closeCursor();
			return '{"erreur":"erreur dans le résultat de la requête}' ;
		}
		/*
		$query = mysqldb::send($query);
		$nl = mysqli_num_rows($query) ;
		if ($nl > 0) {
			$t = mysqli_fetch_array($query) ;
			return $t[0] ; 
		}
		else return false ;*/
	}
	
	public static function send($query) {
		if (mysqldb::$req = mysqldb::$id->query($query)) return true ;
		else return false ;
		
		/*$req = mysqli_query(self::$id, $query);
		if ($req) {
			return $req;
		}
		else {	
			return ("<p id='send'>Erreur dans la requ&ecirc;te : $query </p>") ;
			return 0;
		}*/
	}
	
	public static function fetchall() {
		return mysqldb::$req->fetchAll() ;
	}
	
	public static function getline($table, $num) {
		$query = "SELECT * FROM "."$table WHERE num = $num";
		$query = mysqldb::send($query) ;
		//on vérifie que la réponse n'est pas vide...
		if ($donnees = self::$req->fetch()) {
			self::$req->closeCursor() ;
			return $donnees ;
		}
		else return false ;
		
		/*	
		$query = "SELECT * FROM "."$table WHERE num = $num";
		$query = mysqldb::send($query) ;
		//on vérifie que la réponse n'est pas vide...
		$nl = mysqli_num_rows($query) ;
		if ($nl == 1) return @mysqli_fetch_assoc($query) ;
		else return 0 ;*/
	}
	
	public static function deleteline($table, $num) {
		$query = 'DELETE FROM '.$table.' WHERE num = '.$num ;
		$query = mysqldb::send($query) ;
		return $query ;	
	}
	
	public static function close() {
		self::$req = NULL ;
		self::$id	= NULL ;
	}
	
	public static function setdata($table, $data, $value, $num) {
		$query = 'UPDATE '.$table.' SET $data="'.$value.'" WHERE num='.$num ;
		$query = mysqldb::send($query) ;
		return $query ;
	} 
	
	// La méthode instance
    public static function instance($user="",$host="",$pass="",$base="",$prefixe="") {
		if (!isset(self::$inst)) {
            self::$inst = new mysqldb($user,$host,$pass,$base,$prefixe) ;
        }
        return self::$inst;
    }

    // Prévient les utilisateurs sur le clônage de l'instance
    public function __clone() {
        trigger_error("Le clônage d'une instance _mysqldb n'est pas autorisé.", E_USER_ERROR);
    }	
}
?>