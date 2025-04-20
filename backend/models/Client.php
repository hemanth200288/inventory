<?php
// backend/models/Client.php
class Client {
    private $conn;
    private $table_name = "clients";

    // Client properties
    public $id;
    public $client_name;
    public $client_email;
    public $is_client_inv_req_auto_incre;
    public $order_prefix;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all clients
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create client
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET 
                    client_name = :client_name, 
                    client_email = :client_email, 
                    is_client_inv_req_auto_incre = :is_client_inv_req_auto_incre, 
                    order_prefix = :order_prefix";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_email = htmlspecialchars(strip_tags($this->client_email));
        $this->is_client_inv_req_auto_incre = htmlspecialchars(strip_tags($this->is_client_inv_req_auto_incre));
        $this->order_prefix = htmlspecialchars(strip_tags($this->order_prefix));

        // Bind values
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":client_email", $this->client_email);
        $stmt->bindParam(":is_client_inv_req_auto_incre", $this->is_client_inv_req_auto_incre);
        $stmt->bindParam(":order_prefix", $this->order_prefix);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read one client
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->client_name = $row['client_name'];
            $this->client_email = $row['client_email'];
            $this->is_client_inv_req_auto_incre = $row['is_client_inv_req_auto_incre'];
            $this->order_prefix = $row['order_prefix'];
            return true;
        }

        return false;
    }

    // Update client
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET 
                    client_name = :client_name, 
                    client_email = :client_email, 
                    is_client_inv_req_auto_incre = :is_client_inv_req_auto_incre, 
                    order_prefix = :order_prefix
                  WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_email = htmlspecialchars(strip_tags($this->client_email));
        $this->is_client_inv_req_auto_incre = htmlspecialchars(strip_tags($this->is_client_inv_req_auto_incre));
        $this->order_prefix = htmlspecialchars(strip_tags($this->order_prefix));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":client_email", $this->client_email);
        $stmt->bindParam(":is_client_inv_req_auto_incre", $this->is_client_inv_req_auto_incre);
        $stmt->bindParam(":order_prefix", $this->order_prefix);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete client
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
