<?php
include 'condb.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // ✅ ดึงข้อมูลพนักกงานทั้งหมด
    if ($method === "GET") {
        $stmt = $conn->prepare("SELECT * FROM supply ORDER BY supply_id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $result]);
    }

    // ✅ เพิ่มข้อมูลพนักกงาน
    elseif ($method === "POST") {
        // ตรวจสอบว่าข้อมูลมาจาก JSON หรือ form-data
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (stripos($contentType, "application/json") !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
        } else {
            $data = $_POST;
        }

        // ตรวจสอบค่าว่าง
        if (empty($data["Fristname"]) || empty($data["Lastname"]) || empty($data["phone"]) || empty($data["line"])) {
            echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
            exit;
        }


        // เพิ่มข้อมูลลูกค้า
        $stmt = $conn->prepare("INSERT INTO supply (Fristname, Lastname, phone, line)
                                VALUES (:Fristname, :Lastname, :phone, :line)");

        $stmt->bindParam(":Fristname", $data["Fristname"]);
        $stmt->bindParam(":Lastname", $data["Lastname"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":line", $data["line"]);
      

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "เพิ่มตัวแทนจำหน่ายเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ส่งข้อมูลReportไม่สำเร็จ"]);
        }
    }

    // ✅ แก้ไขข้อมูล
    elseif ($method === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["supply_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบค่า supply_id"]);
            exit;
        }

        $supply_id = intval($data["supply_id"]);

            $sql = "UPDATE supply 
                    SET Fristname = :Fristname, 
                        Lastname = :Lastname, 
                        phone = :phone, 
                        line = :line
                    WHERE supply_id = :id";
        

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":Fristname", $data["Fristname"]);
        $stmt->bindParam(":Lastname", $data["Lastname"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":line", $data["line"]);
        $stmt->bindParam(":id", $supply_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถแก้ไขข้อมูลได้"]);
        }
    }

    // ✅ ลบข้อมูล
    elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["supply_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบค่า supply_id"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM supply WHERE supply_id = :id");
        $stmt->bindParam(":id", $data["supply_id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
        }
    }

    else {
        echo json_encode(["success" => false, "message" => "Method ไม่ถูกต้อง"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>