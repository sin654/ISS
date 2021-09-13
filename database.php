<?php

class DatabaseActions
{
    private $pdo;
    private $lastError;

    function __construct()
    {
        $this->pdo = $this->connect_db();
        $this->lastError = NULL;
    }


    function connect_db()   // připojení databáze
    {
        
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=xdolez81;port=/var/run/mysql/mysql.sock", 'xdolez81', 'emnane6e');
        } catch (PDOException $e) {
            echo "Connection error: ".$e->getMessage();
            die();
        }
        return $pdo;
        
/*
        $dsn = 'mysql:host=localhost;dbname=discord';
        $username = 'demo';
        $password = 'demo';
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
*/
    }

    /**
     * Vrací chybu  z poledního příkazu, prázdný řetězec, pokud žádná chyba nenastala
     * @return mixed|string
     */
    function getErrorMessage()
    {
        if ($this->lastError === NULL)
            return '';
        else
            return $this->lastError[2]; //the message
    }

    /*
     * INSERT -------------------------------------------------------------------------------------------------
     * */

    /**
     * Vložení nového registrovaného uživatele do tabulky "Uzivatel"
     * @param $data - array("nick" => string, "heslo" => string)
     * @return bool
     */
    function add_user($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Uzivatel(nick, heslo) VALUES (:nick, :heslo)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Vytvoření nové skupiny a vložení jejího tvůrce->správce do vazební tabulky "Clen_skupiny"
     * @param $data - array("id_spravce" => int, "nazev_skupina" => string)
     * @return bool
     */
    function add_group($data) // vložení nové skupiny + umístění správce do čenů skupiny (funguje)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Skupina(id_spravce, nazev_skupina) VALUES (:id_spravce, :nazev_skupina)');
        if ($stmt->execute($data))  // povedlo se vložit do tabulky Uzivatel
        {
            $id_skupina = $this->pdo->lastInsertId();   // zjištění generovaného id nově vloženého uživatele
            $stmt = $this->pdo->prepare('INSERT INTO Clen_skupiny(id_uzivatel, id_skupina) VALUES (?,  ?)');
            if ($stmt->execute(array($data["id_spravce"], $id_skupina))) // vložení záznamu o členovi skupiny do "Clen_skupiny"
            {
                return TRUE;
            }
            else // nepovedlo se vlozeni
            {
                $this->lastError = $stmt->errorInfo();
                return FALSE;
            }
        } else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Vytvoření nového vlánka ve skupině
     * @param $data - array("id_skupina => int, "nazev_vlakno" => string)
     * @return bool
     */
    function add_thread($data) // vložení nového vlákna
    {
        $stmt = $this->pdo->prepare('INSERT INTO Vlakno(id_skupina, nazev_vlakno) VALUES (:id_skupina, :nazev_vlakno)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vytvoreni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Vytvoření nového příspěvku ve vlákně
     * @param $data - array("id_vlakno" => int, "id_uzivatel" = > int, "text" => string) ! id_uzivatel patří tvůrci příspěvku
     * @return bool
     */
    function add_contribution($data)    // vytvoření nového příspěvku
    {
        $stmt = $this->pdo->prepare('INSERT INTO Prispevek(id_vlakno, id_uzivatel, text) VALUES (:id_vlakno, :id_uzivatel, :text)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Přidání nového člena skupiny
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function add_member($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Clen_skupiny(id_skupina, id_uzivatel) VALUES (:id_skupina, :id_uzivatel)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Přidání nového moderátora skupiny
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function add_mod($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Mod_skupiny(id_skupina, id_uzivatel) VALUES (:id_skupina, :id_uzivatel)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Vytvoření nového administrátora
     * @param $data - array("id_uzivatel" => int)
     * @return bool
     */
    function add_admin($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Admin(id_uzivatel) VALUES (:id_uzivatel)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Uživatel ohodnotil příspěvek (uchovává pouze informace o tom, kdo již příspěvek hodnotil) ukládá záznam o tom, že již hodnotil
     * @param $data - array("id_prispevek" => int, "id_uzivatel" => int)
     * @return bool
     */
    function add_user_ranked_contribution($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Prispevek_rank(id_prispevek, id_uzivatel) VALUES (:id_prispevek ,:id_uzivatel)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }


    /**
     * Žádost o členství ve skupině
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function add_request_member($data)
    {
        // TODO check jestli jiz request nebyl podán
        $stmt = $this->pdo->prepare('INSERT INTO Zadost(id_skupina, id_uzivatel, typ_zadost) VALUES (:id_skupina ,:id_uzivatel, 0)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Žádost o post moderátora ve skupině
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function add_request_mod($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Zadost(id_skupina, id_uzivatel, typ_zadost) VALUES (:id_skupina ,:id_uzivatel, 1)');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se vlozeni
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /*
     * DELETE -----------------------------------------------------------------------------------------------------------
     * */

    /**
     * Smazání uživatele, maže rekurzivně všechny svoje aktivity (vlastněné skupiny>vlánka>příspěvky) a členství
     * @param $data - array("id_uzivatel" => int)
     * @return bool
     */
    function delete_user($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Uzivatel WHERE id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Smazání skupiny, rekurzivně jejich vláken a příspěvků (o to se stará ON DELETE CASCADE)
     * @param $data - array("id_skupina" => int)
     * @return bool
     */
    function delete_group($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Skupina WHERE id_skupina=:id_skupina');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Smazání vlákna, rekurzivně smaže všechny příspěvky v něm
     * @param $data - array("id_vlakno" => int)
     * @return bool
     */
    function delete_thread($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Vlakno WHERE id_vlakno=:id_vlakno');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Smazání příspěvku
     * @param $data - array("id_prispevek" => int)
     * @return bool
     */
    function delete_contribution($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Prispevek WHERE id_prispevek=:id_prispevek');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }


    /**
     * Uživatel opouští skupinu + přestává být jejím moderátorem + pokud je správcem skupiny -> skupina je smazána
     * @param $data - array("id_skupina" => int, "id_uzivatel"  => int)
     * @return bool
     */
    function leave_group($data)
    {
        // člen opustí skupinu a pokud je i mod skupiny, tak ním přestane být
        $stmt = $this->pdo->prepare('DELETE FROM Clen_skupiny WHERE id_skupina=:id_skupina AND id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
        $stmt = $this->pdo->prepare('DELETE FROM Mod_skupiny WHERE id_skupina=:id_skupina AND id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
        // pokud skupinu upouští její správce -> skupina je smazána
        $group = $this->select_group(array("id_skupina" => $data["id_skupina"]));
        if ($group["id_spravce"] == $data["id_uzivatel"])
        {
            $this->delete_group(array("id_skupina" => $data["id_skupina"]));
            return TRUE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
     * Smaže moderátora skupiny (demote)
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function delete_mod($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Mod_skupiny WHERE id_skupina=:id_skupina AND id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Odstraní administrátora (asi se nebude používat!)
     * @param $data - array("id_uzivatel" => int)
     * @return bool
     */
    function delete_admin($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Admin WHERE id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Smaže požadavek na člensví uživatele ve skupině
     * @param $data - array("id_uzivatel" => int, "id_skupina" => int)
     * @return bool
     */
    function delete_request_member($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Zadost WHERE id_uzivatel=:id_uzivatel AND id_skupina=:id_skupina AND typ_zadost=0');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Smaže požadavek na člensví uživatele ve skupině
     * @param $data - array("id_uzivatel" => int, "id_skupina" => int)
     * @return bool
     */
    function delete_request_mod($data)
    {
        $stmt = $this->pdo->prepare('DELETE FROM Zadost WHERE id_uzivatel=:id_uzivatel AND id_skupina=:id_skupina AND typ_zadost=1');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }


    /*
     * SELECT - MODIFY na Uzivatel ------------------------------------------------------------------------------------
     * */

    /**
     * Select do databáze na konkrétního registrovaného uživatele definovaného parametrem
     * @param $data - array("id_uzivatel" => int)
     * @return mixed - array s řádkem z Uzivatel
     */
    function select_user_by_id($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Uzivatel WHERE id_uzivatel=:id_uzivatel');
        $stmt->execute($data);
        return $stmt->fetch();
    }

    /**
     * Select do databáze na konkrétního registrovaného uživatele definovaného parametrem
     * @param $data - array("nick" => string)
     * @return mixed - array s řádkem z Uzivatel
     */
    function select_user_by_nick($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Uzivatel WHERE nick=:nick');
        $stmt->execute($data);
        return $stmt->fetch();
    }

    /**
     * Select do databáze na všechny registrované uživatele
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function select_all_users()
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Uzivatel');
        $stmt->execute();
        return $stmt;
    }

    /**
     * Změní popis uživatele (atribut popis_uzivatel)
     * @param $data - array("id_uzivatel" => int, "popis_uzivatel" => string)
     * @return bool
     */
    function modify_user_description($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Uzivatel SET popis_uzivatel=:popis_uzivatel WHERE id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Změní heslo uživatele (atribut heslo)
     * @param $data - array("id_uzivatel" => int, "heslo" => string)
     * @return bool
     */
    function modify_user_password($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Uzivatel SET heslo=:heslo WHERE id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Změní práva pro zobrazní profilu uživatele (atribut prava_uzivatel)
     * @param $data - array("id_uzivatel" => int, "prava_uzivatel" => int) 1/2/3 = skupina/registrovaní/všichni
     * @return bool
     */
    function modify_user_permissions($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Uzivatel SET prava_uzivatel=:prava_uzivatel WHERE id_uzivatel=:id_uzivatel');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }



    /*
     * SELECT - MODIFY na Skupina -----------------------------------------------------------------------------------
     * */
    /**
     * Select do databáze na konkrétní skupinu definovanou parametrem
     * @param $data - array("id_skupina" => int)
     * @return mixed
     */
    function select_group($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Skupina WHERE id_skupina=:id_skupina');
        $stmt->execute($data);
        return $stmt->fetch();
    }

    /**
     * Select do databáze na všechny skupiny
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function select_all_groups()
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Skupina');
        $stmt->execute();
        return $stmt;
    }

    /**
     * Změní popis skupiny (atribut popis_skupina)
     * @param $data - array("id_skupina" => int, "popis_skupina" => string)
     * @return bool
     */
    function modify_group_description($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Skupina SET popis_skupina=:popis_skupina WHERE id_skupina=:id_skupina');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Změní práva na zobrazení obsahu skupiny (atribut prava_skupina)
     * @param $data - array("id_skupina" => int, "prava_skupina" => int)
     * @param $data - 1/2/3 = skupina/registrovaní/všichni
     * @return bool
     */
    function modify_group_permissions($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Skupina SET prava_skupina=:prava_skupina WHERE id_skupina=:id_skupina');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }


    /*
     * SELECT a MODIFY na Vlakno
     * */

    /**
     * Select na určité vlákno, přes id_vlakno
     * @param $data - array("id_vlakno" => int)
     * @return mixed
     */
    function select_thread($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Vlakno WHERE id_vlakno=:id_vlakno');
        $stmt->execute($data);
        return $stmt->fetch();
    }


    /**
     * Select do databáze na všechny vlákna v dané skupině
     * @param $data - array("id_skupina" => int)
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function select_all_threads_in_group($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Vlakno WHERE id_skupina=:id_skupina');
        $stmt->execute($data);
        return $stmt;
    }

    /**
     * Změní jméno vlákna (atribut: nazev_vlakno)
     * @param $data - array("id_vlakno" => int, "nazev_vlakno" => string)
     * @return bool
     */
    function modify_thread_name($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Vlakno SET nazev_vlakno=:nazev_vlakno WHERE id_vlakno=:id_vlakno');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }


    /*
     * SELECT a MODIFY na Prispevek
     * */
    /**
     * Select do databáze na konkrétní příspěvek
     * @param $data - array("id_prispevek" => int)
     * @return array
     */
    function get_contribution($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Prispevek WHERE id_prispevek=:id_prispevek');
        $stmt->execute($data);
        return $stmt->fetch();
    }

    /**
     * Select do databáze na všechny příspěvky ve vlákně definovaném parametrem
     * @param $data - array("id_vlakno" => int)
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function get_all_contributions($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Prispevek WHERE id_vlakno=:id_vlakno');
        $stmt->execute($data);
        return $stmt;
    }

    /**
     * Změna obsahu příspěvku (atribut: text)
     * @param $data - array("id_prispevek" => int, "text" => string)
     * @return bool
     */
    function modify_contribution_text($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Prispevek SET text=:text WHERE id_prispevek=:id_prispevek');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Změna ranku příspěvku +1 = like (atribut: rank)
     * @param $data - array("id_prispevek" => int)
     * @return bool
     */
    function modify_contribution_like($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Prispevek SET rank=rank +1 WHERE id_prispevek=:id_prispevek');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /**
     * Změna ranku příspěvku -1 = dislike (atribut: rank)
     * @param $data - array("id_prispevek" => int)
     * @return bool
     */
    function modify_contribution_dislike($data)
    {
        $stmt = $this->pdo->prepare('UPDATE Prispevek SET rank=rank -1 WHERE id_prispevek=:id_prispevek');
        if ($stmt->execute($data))
        {
            return TRUE;
        }
        else // nepovedlo se smazat
        {
            $this->lastError = $stmt->errorInfo();
            return FALSE;
        }
    }

    /*
     * SELECT na Zadost
     * */

    /**
     * Vrátí všechny požadavky na vstup do dané skupiny
     * @param $data - array("id_skupina" => int)
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function get_all_group_request_for_member($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Zadost WHERE id_skupina=:id_skupina AND typ_zadost=0');
        $stmt->execute($data);
        return $stmt;
    }

    /**
     * Vrátí všechny žádosti o post moderátora v dané skupině
     * @param $data - array("id_skupina" => int)
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function get_all_group_request_for_mod($data)
    {
        $stmt = $this->pdo->prepare('SELECT *  FROM Zadost WHERE id_skupina=:id_skupina AND typ_zadost=1');
        $stmt->execute($data);
        return $stmt;
    }

    /**
     * Přijme žádost uživatele o vstup do skupiny
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function accept_request_user($data)
    {
        if ($this->add_member($data) and $this->delete_request_member($data))
        {
            return TRUE;
        }
        else    // zmazání požadavku, nebo přidání uživatele se nezdařilo
        {
            return FALSE;
        }

    }

    /**
     * Přijme žádost člena skupiny o pozici moderátora
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function accept_request_mod($data)
    {
        if ($this->add_mod($data) and $this->delete_request_mod($data))
        {
            return TRUE;
        }
        else    // zmazání požadavku, nebo přidání uživatele se nezdařilo
        {
            return FALSE;
        }
    }

    /*
     * SELECT obecné funkce
     * */

    /**
     * Select do databáze na všechny skupiny, jejichž je uzivatel členem
     * @param $data - array("id_uzivatel" => int)
     * @return bool|PDOStatement - PDOdatabase Statement, use ->fetch() to get one line as associative array
     */
    function select_user_groups($data)
    {
        $stmt = $this->pdo->prepare('SELECT Skupina.id_skupina, id_spravce, nazev_skupina, popis_skupina, prava_skupina  FROM Clen_skupiny, Skupina WHERE Clen_skupiny.id_skupina = Skupina.id_skupina AND Clen_skupiny.id_uzivatel=:id_uzivatel');
        $stmt->execute($data);
        return $stmt;
    }

    /**
     * Je uživatel moderátor skupiny?
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function is_user_group_mod($data)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Mod_skupiny WHERE id_skupina=:id_skupina AND id_uzivatel=:id_uzivatel');
        $stmt->execute($data);
        if($stmt->fetch()) // existuje záznam, že je uživatel mod skupiny?
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Je uživatel správce skupiny?
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function is_user_group_manager($data)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Skupina WHERE id_skupina=:id_skupina AND id_spravce=:id_uzivatel');
        $stmt->execute($data);
        if($stmt->fetch())
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Je uživatel člen skupiny?
     * @param $data - array("id_skupina" => int, "id_uzivatel" => int)
     * @return bool
     */
    function is_user_group_member($data)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Clen_skupiny WHERE id_skupina=:id_skupina AND id_uzivatel=:id_uzivatel');
        $stmt->execute($data);
        if($stmt->fetch())
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Je uživatel autorem příspěvku?
     * @param $data - array("id_prispevek" => int, "id_uzivatel" => int)
     * @return bool
     */
    function is_user_contribution_author($data)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Prispevek WHERE id_prispevek=:id_prispevek AND id_uzivatel=:id_uzivatel');
        $stmt->execute($data);
        if($stmt->fetch())
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Je uživatel administrátor?
     * @param $data - array("nick" => string)
     * @return bool
     */
    function is_user_administrator($data)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Admin, Uzivatel WHERE Uzivatel.id_uzivatel=Admin.id_uzivatel AND Uzivatel.nick=:nick');
        $stmt->execute($data);
        if($stmt->fetch())
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Jsou uživatelé členy nějaké stejné skupiny? Kvůli právům na prohlížení profilů
     * @param $data - array("id_uzivatel1" => int, "id_uzivatel2" => int)
     * @return bool
     */
    function does_users_share_group($data) // TODO
    {
        // zjistím všechny skupiny pro každého uživatele
        $user1 = $this->select_user_groups(array("id_uzivatel" => $data["id_uzivatel1"]));
        $user2 = $this->select_user_groups(array("id_uzivatel" => $data["id_uzivatel2"]));

        // vytvoření pouze polí id skupin pro uživatele
        $group_id_array_1 = array();
        $group_id_array_2 = array();
        while ($value = $user1->fetch())
        {
            array_push($group_id_array_1, $value["id_skupina"]);
        }
        while ($value = $user2->fetch())
        {
            array_push($group_id_array_2, $value["id_skupina"]);
        }

        // teď už mám 2 pole pouze hodnot, ty stačí porovnat
        $diff_count = count(array_diff($group_id_array_1, $group_id_array_2));    // kolik
        $original_count = count($group_id_array_1);
        if ($original_count == $diff_count) // není ani jediddná hodnota id_skupina v poli1, která by byla v poli 2 -> žádná společná skupina
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

}

?>