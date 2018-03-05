<?php
class Show {
	private $conn;
	private $success;
	private $message = "";
	
	public $title;
	public $description;
	public $duration;
	public $originalAirDate;
	public $rating;
	public $keywords;
	
	public function __construct($conn) {
        $this->conn = $conn;
    }
	
	function createShow() {
		$this->validateShowProperties();


		if($this->success) {
			try {
				$sql = "insert into shows (title, description, duration, originalAirDate, rating, keywords) values (:title,:description,:duration,:originalAirDate,:rating,:keywords)";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindParam("title", $this->sanitizeXss($this->title));
				$stmt->bindParam("description", $this->sanitizeXss($this->description));
				$stmt->bindParam("duration", $this->sanitizeXss($this->duration)); 
				$stmt->bindParam("originalAirDate", $this->sanitizeXss($this->originalAirDate));
				$stmt->bindParam("rating", $this->sanitizeXss($this->rating));
				$stmt->bindParam("keywords", $this->sanitizeXss($this->keywords));

				if($stmt->execute()){
					$this->message = "Show created successfully";
				}
			} catch(PDOException $e) {
				$this->success = FALSE;
				if($e->errorInfo[1] == 1062) {
					$this->message = "Title already exists";
				} else {
					$this->message = "Error creating show";
				}
			} 
		} 
		
		
		$this->returnResponse();
	}
	
	function listShows($showsPerPage, $pageNumber, $sortBy, $sortDirection) {
		$this->validateListProperties($showsPerPage, $pageNumber, $sortBy, $sortDirection);

		if($this->success) {
			try {
				$shows = array();
				$limitStart = $this->getLimitStart($pageNumber, $showsPerPage);
				$sql = "select id, title, description, duration, originalAirDate, rating, keywords from shows order by :sortBy :sortDirection limit :limitStart, :showsPerPage";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindParam("sortBy", $this->sanitizeXss($sortBy));
				$stmt->bindParam("sortDirection", $this->sanitizeXss($sortDirection));
				$stmt->bindParam("limitStart", $limitStart, PDO::PARAM_INT);
				$stmt->bindParam("showsPerPage", $showsPerPage, PDO::PARAM_INT);

				if($stmt->execute()) {
					while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$show = array("id" => $row["id"],
										"description" => $row["description"],
										"duration" => $row["duration"],
										"originalAirDate" => $row["originalAirDate"],
										"rating" => $row["rating"],
										"keywords" => $row["keywords"]);
						array_push($shows, $show);
					}
					$this->message = $shows;
				}
			} catch(PDOException $e) {
				$this->success = FALSE;
				$this->message = "Error listing shows";
			} 
		}
		
		$this->returnResponse();
	}
	
	function updateShow() {
		$this->validateId();
		$this->validateShowProperties();

		if($this->success) {
			try {
				$sql = "update shows set title=:title, description=:description, duration=:duration, originalAirDate=:originalAirDate, rating=:rating, keywords=:keywords where id=:id";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindParam("title", $this->sanitizeXss($this->title));
				$stmt->bindParam("description", $this->sanitizeXss($this->description));
				$stmt->bindParam("duration", $this->sanitizeXss($this->duration)); 
				$stmt->bindParam("originalAirDate", $this->sanitizeXss($this->originalAirDate));
				$stmt->bindParam("rating", $this->sanitizeXss($this->rating));
				$stmt->bindParam("keywords", $this->sanitizeXss($this->keywords));
				$stmt->bindParam("id", $this->sanitizeXss($this->id));

				if($stmt->execute()){
					$this->message = "Show update successfully";
				}
			} catch(PDOException $e) {
				$this->success = FALSE;
				if($e->errorInfo[1] == 1062) {
					$this->message = "Title already exists";
				} else {
					$this->message = "Error updating show";
				}
			} 
		}
		
		$this->returnResponse();
	}
	
	function validateId() {
		if(!is_numeric($this->id) || ($this->id < 0 || $this->id > 18446744073709551615)) {
			$this->addSeparator();
			$this->message .= "id";
		}
		
		$this->updateSuccessFlag();
	}

	function validateShowProperties() {
		if(mb_strlen($this->title, 'utf8') > 255) {
			$this->addSeparator();			
			$this->message .= "title";
		}

		if(mb_strlen($this->description, 'utf8') > 255) {
			$this->addSeparator();
			$this->message .= "description";
		}

		if(!is_numeric($this->duration) || ($this->duration < 0 || $this->duration > 4294967295)) {
			$this->addSeparator();
			$this->message .= "duration";
		}

		if(!$this->isValidDate($this->originalAirDate)) {
			$this->addSeparator();
			$this->message .= "date";
		}
		
		if(!is_numeric($this->rating) || ($this->rating < 0 || $this->rating > 10)) {
			$this->addSeparator();
			$this->message .= "rating";
		}
		
		if(mb_strlen($this->keywords, 'utf8') > 255) {
			$this->addSeparator();
			$this->message .= "keywords";
		}
		
		$this->updateSuccessFlag();
	}
	
	function validateListProperties($showsPerPage, $pageNumber, $sortBy, $sortDirection) {
		$validSortBy = array("id","title","description","duration","originalAirDate","rating","keywords");
		$validSortDirection = array("ASC", "DESC");
		if(!is_numeric($showsPerPage) || $showsPerPage < 1) {
			$this->addSeparator();
			$this->message .= "showsPerPage";
		}
		
		if(!is_numeric($pageNumber) || $pageNumber < 1) {
			$this->addSeparator();
			$this->message .= "pageNumber";
		}
		
		if(!is_numeric($showsPerPage) || $showsPerPage < 1) {
			$this->addSeparator();
			$this->message .= "showsPerPage";
		}
		
		if(!in_array($sortBy, $validSortBy)) {
			$this->addSeparator();
			$this->message .= "sortBy";
		}
		
		if(!in_array($sortDirection, $validSortDirection)) {
			$this->addSeparator();
			$this->message .= "sortDirection";
		}
		
		$this->updateSuccessFlag();
	}
	
	function updateSuccessFlag() {
		if($this->message == "") {
			$this->success = true;
		} else {
			$this->success = false;
		}
	}
	
	function addSeparator() {
		if($this->message != "") {
			$this->message .= ", ";
		} else {
			$this->message .= "Invalid ";
		}
	}
	
	function isValidDate($date) {
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
			return true;
		} else {
			return false;
		}
	}
	
	function getLimitStart($pageNumber, $showsPerPage) {
		if($pageNumber > 1) {
			return ($pageNumber - 1) * $showsPerPage;
		}
		
		return 0;
	}
	
	function sanitizeXss($value) {
		return htmlspecialchars(strip_tags($value));
	}
	
	function returnResponse() {
		$response = array(
			"success" =>  $this->success,
			"message" => $this->message
		);
		print_r(json_encode($response, JSON_UNESCAPED_SLASHES));
	}
}
?>