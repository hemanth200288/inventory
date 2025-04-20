<?php
// backend/models/Invoice.php
class Invoice {
    private $conn;
    private $table_name = "invoices";

    // Invoice properties
    public $id;
    public $client_id;
    public $order_no;
    public $order_date;
    public $payment_method;

    // Additional client info
    public $client_name;
    public $client_email;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all invoices
    public function read() {
        $query = "SELECT i.id, i.order_no, i.order_date, i.payment_method, 
                         c.client_name, c.client_email, c.order_prefix 
                  FROM " . $this->table_name . " i
                  LEFT JOIN clients c ON i.client_id = c.id
                  ORDER BY i.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create invoice
    public function create() {
        // Check if we need to generate an order number
        if($this->isAutoIncrement()) {
            $prefix = $this->getOrderPrefix();
            $this->order_no = $this->generateOrderNo($prefix);
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET 
                    client_id = :client_id, 
                    order_no = :order_no, 
                    order_date = :order_date, 
                    payment_method = :payment_method";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));
        $this->order_no = htmlspecialchars(strip_tags($this->order_no));
        $this->order_date = htmlspecialchars(strip_tags($this->order_date));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));

        // Bind values
        $stmt->bindParam(":client_id", $this->client_id);
        $stmt->bindParam(":order_no", $this->order_no);
        $stmt->bindParam(":order_date", $this->order_date);
        $stmt->bindParam(":payment_method", $this->payment_method);

        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Read one invoice
    public function readOne() {
        $query = "SELECT i.id, i.client_id, i.order_no, i.order_date, i.payment_method,
                         c.client_name, c.client_email
                  FROM " . $this->table_name . " i
                  LEFT JOIN clients c ON i.client_id = c.id
                  WHERE i.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->client_id = $row['client_id'];
            $this->order_no = $row['order_no'];
            $this->order_date = $row['order_date'];
            $this->payment_method = $row['payment_method'];
            
            // Add client data
            $this->client_name = $row['client_name'];
            $this->client_email = $row['client_email'];
            
            return true;
        }

        return false;
    }

    // Check if auto increment is enabled for client
    private function isAutoIncrement() {
        $query = "SELECT is_client_inv_req_auto_incre FROM clients WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->client_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($row && $row['is_client_inv_req_auto_incre'] == 1);
    }

    // Get order prefix for client
    private function getOrderPrefix() {
        $query = "SELECT order_prefix FROM clients WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->client_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($row) ? $row['order_prefix'] : '';
    }

    // Generate order number
    private function generateOrderNo($prefix) {
        // Count invoices for this client to determine the next number
        $query = "SELECT COUNT(*) as invoice_count FROM " . $this->table_name . " WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->client_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = ($row) ? $row['invoice_count'] + 1 : 1;
        return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Get invoices by client
    public function getInvoicesByClient() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE client_id = ? ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->client_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Update invoice
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET 
                    client_id = :client_id, 
                    order_no = :order_no, 
                    order_date = :order_date, 
                    payment_method = :payment_method
                  WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));
        $this->order_no = htmlspecialchars(strip_tags($this->order_no));
        $this->order_date = htmlspecialchars(strip_tags($this->order_date));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":client_id", $this->client_id);
        $stmt->bindParam(":order_no", $this->order_no);
        $stmt->bindParam(":order_date", $this->order_date);
        $stmt->bindParam(":payment_method", $this->payment_method);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete invoice
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
