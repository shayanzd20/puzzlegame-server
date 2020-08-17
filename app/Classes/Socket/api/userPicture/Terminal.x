<?php
/****************************************************************************************
*  	Database Terminal Manager (version 1.0)			*  	This library used for managing	*
*  	Write @ : 10/8/2014								*	wide range of databases. all	*
*  	Last Modify : -----								*	needed function are included	*
*  	Written by : Omid Golshan (OMID.GT21@GMAIL.COM)	*	and we added more database 		*
*  	Licensed by : Omid Golshan						*	support shortly.				*
*	Website : http://zhairyn.com					*									*
****************************************************************************************/


class Terminal {
	private
		$Host="localhost",
		$Username="balootmo_smooti",
		$Password="@g)p69h_;(p]";
	private
		$connection,
		$result;
	
	function __construct($DB)
	{
		// global $setting;
		try {
			$this->connection=new PDO(
				"mysql:host=$this->Host;dbname=$DB",
				$this->Username,
				$this->Password,
				array ( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
						PDO::ATTR_PERSISTENT => true)
			);
			
			
		} catch(PDOException $e) {
			die("متاسفانه درحال حاضر امکان برقراری ارتباط با سامانه مرکزی وجود ندارد لطفا بعدا دوباره تلاش کنید". $e->getMessage());
		}
	
	}
	
	function Run($query)
	{
		$this->result = $this->connection->query($query);
	}
	
	function Shell($query)
	{
		return $this->connection->query($query);
	}
	
	function Size($result=NULL)
	{
		if(!$result)
		{
			if($this->result) 
			{
				return $this->result->rowCount();
			} 
			else 
			{
				return FALSE;
			}
		}
		
		return $result->rowCount();
	}
	
	function Load($result=NULL)
	{
		if(!$result)
			return $this->result->fetch(PDO::FETCH_OBJ);
		
		return $result->fetch(PDO::FETCH_OBJ);
	}
	
	function Buffer($result=NULL)
	{
		if(!$result)
			return $this->result->fetchAll(PDO::FETCH_OBJ);
		
		return $result->fetchAll(PDO::FETCH_OBJ);
	}

    function LastID()
    {
        $result=$this->connection->lastInsertId();

        return $result;
    }
}

?>
