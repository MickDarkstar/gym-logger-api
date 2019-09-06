<?php
final class ExerciseRepository extends BaseRepository
{
    private const DB_TABLE = "users";

    public function __construct(PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    private function mapToModel($row)
    {
        $exercise = new Exercise(
            $row['id'],
            $row['createdByUserId'],
            $row['muscleId'],
            $row['name'],
            $row['type'],
            $row['level']
        );
        $exercise->created = $row['created'];
        $exercise->modifiedByUserId = $row['modifiedByUserId'];
        $exercise->modified = $row['modified'];
        return $exercise;
    }


    private function mapToModels($rows)
    {
        $models = [];
        foreach ($rows as $row) {
            $object = self::mapToModel($row);
            array_push($models, $object);
        }
        return $models;
    }

    public function validate(Exercise $exercise)
    {
        if ($exercise->createdByUserId === null) {
            http_response_code(400);
            echo json_encode(array("message" => "CreatedByUserId is required to create exercise. "));
            return false;
        }

        if ($exercise->name === null) {
            http_response_code(400);
            echo json_encode(array("message" => "Name is required to create exercise. "));
            return false;
        }

        if ($exercise->type === null) {
            http_response_code(400);
            echo json_encode(array("message" => "Type is required to create exercise."));
            return false;
        }

        // Not yet implemented
        // if ($exercise->muscle === null) {
        //     http_response_code(400);
        //     echo json_encode(array("message" => "Muscle is required to create exercise."));
        //     return false;
        // }

        if ($exercise->level === null || $exercise->level === "") {
            http_response_code(400);
            echo json_encode(array("message" => "Level is required to create exercise."));
            return false;
        }
        return true;
    }


    public function exerciseExist(int $id)
    {
        $query = "SELECT id password
                    FROM " . self::DB_TABLE . "
                    WHERE id = ?
                    LIMIT 0,1";

        $stmt = self::$dbHandle->prepare($query);

        $stmt->bindParam(1, $id);

        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            return true;
        }
        return false;
    }

    /**
     * Not implemented
     */
    public function muscleExists(int $muscleId)
    {
        // query to check if email exists
        $query = "SELECT id password
                    FROM " . self::DB_TABLE . "
                    WHERE id = ?
                    LIMIT 0,1";

        // prepare the query
        $stmt = self::$dbHandle->prepare($query);

        // sanitize
        $muscleId = htmlspecialchars(strip_tags($muscleId));

        // bind given muscleId value
        $stmt->bindParam(1, $muscleId);

        // execute the query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // if email exists, assign values to object properties for easy access and use for php sessions
        if ($num > 0) {
            // return true because muscleId exists in the database
            return true;
        }

        // return false if muscleId does not exist in the database
        return false;
    }

    public function create(Exercise $exercise)
    {
        $query = "INSERT INTO " . $this->table_name . "
        SET
            createdByUserId = :createdByUserId,
            name = :name,
            type = :type,
            muscleId = :muscleId";

        $stmt = self::$dbHandle->prepare($query);

        $stmt->bindParam(':createdByUserId', $exercise->createdByUserId);
        $stmt->bindParam(':name', $exercise->name);
        $stmt->bindParam(':type', $exercise->type);
        $stmt->bindParam(':muscleId', $exercise->muscleId);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById(string $id)
    {
        $query = "SELECT *
                    FROM " . $this->table_name . "
                    WHERE id = ?
                    LIMIT 0,1";

        $stmt = self::$dbHandle->prepare($query);

        $stmt->bindParam(1, $id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return self::mapToModel($row);
    }

    public function update(Exercise $exercise)
    {
        $query = "UPDATE " . $this->table_name . "
            SET
                name = :name,
                type = :type,
                muscleId = :muscleId,
                level = :level
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $exercise->name);
        $stmt->bindParam(':type', $exercise->type);
        $stmt->bindParam(':muscleId', $exercise->muscleId);
        $stmt->bindParam(':level', $exercise->level);

        $stmt->bindParam(':id', $exercise->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
    
    public function getAll()
    {
        $query = "SELECT *
        FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return self::mapToModels($rows);
    }
}