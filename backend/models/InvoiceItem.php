<?php
// backend/models/InvoiceItem.php
class InvoiceItem {
    private $conn;
    private $table_name = "invoice_items";

    // Invoice item properties
    public $id;
    public $invoice_id;
    public $task_desc;
    public $price;
    public $is_subtask;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read items by invoice
    public function readByInvoice() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE invoice_id = ? 
                  ORDER BY id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->invoice_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Create invoice item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET 
                    invoice_id = :invoice_id, 
                    task_desc = :task_desc, 
                    price = :price, 
                    is_subtask = :is_subtask";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->invoice_id = htmlspecialchars(strip_tags($this->invoice_id));
        $this->task_desc = htmlspecialchars(strip_tags($this->task_desc));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->is_subtask = htmlspecialchars(strip_tags($this->is_subtask));

        // Bind values
        $stmt->bindParam(":invoice_id", $this->invoice_id);
        $stmt->bindParam(":task_desc", $this->task_desc);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":is_subtask", $this->is_subtask);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Calculate invoice total
    public function calculateInvoiceTotal() {
        $query = "SELECT SUM(price) as total FROM " . $this->table_name . " WHERE invoice_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->invoice_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($row) ? $row['total'] : 0;
    }

    // Update invoice item
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET 
                    task_desc = :task_desc, 
                    price = :price, 
                    is_subtask = :is_subtask
                  WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->task_desc = htmlspecialchars(strip_tags($this->task_desc));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->is_subtask = htmlspecialchars(strip_tags($this->is_subtask));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":task_desc", $this->task_desc);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":is_subtask", $this->is_subtask);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete invoice item
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

    // Delete all items for an invoice
    public function deleteByInvoice() {
        $query = "DELETE FROM " . $this->table_name . " WHERE invoice_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->invoice_id = htmlspecialchars(strip_tags($this->invoice_id));
        $stmt->bindParam(1, $this->invoice_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
