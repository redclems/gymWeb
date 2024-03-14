import mysql.connector
from datetime import datetime
#import script

# connexion à la base de données
mydb = mysql.connector.connect(
  host='localhost',
  user='gymweb',
  passwd='sere',
  database='gymweb'
)
mycursor = mydb.cursor()


# une requete pour creer une personne

def creer_personne(nom, prenom):
    name = nom
    pre = prenom
    now = datetime.now()
    formatted_date = now.strftime('%Y-%m-%d %H:%M:%S')
    #requete SQL pour inserer des données
    sql = "INSERT INTO personne(nom, prenom,date) VALUE( \" "+ name+" \", \" " +pre+" \",\" " +formatted_date+" \" )"
    print(sql)
    #exécuter la requete SQL
    mycursor.execute(sql)
    #validation de la transaction
    mydb.commit()

    print(mycursor.rowcount, "record inserted.")

def get_nom_prenom_par_id(id_personne):
    sql = "SELECT nom, prenom FROM personne WHERE id = %s"
    mycursor.execute(sql, (id_personne,))

    result = mycursor.fetchone()
    if result:
        return result[0], result[1]
    else:
        return None, None

def creer_activity(id_personne, id_nom_action):
    # Définition des données à insérer
    now = datetime.now()
    formatted_date = now.strftime('%Y-%m-%d %H:%M:%S')
    # Requête SQL pour insérer les données
    sql = "INSERT INTO activiter(id_personne, date_debut, id_nom_action) VALUES (\" "+ str(id_personne)+" \", \" " +formatted_date+" \",\" " +str(id_nom_action)+" \" )"

    # Exécution de la requête
    mycursor.execute(sql)

    # Validation de la transaction
    mydb.commit()

    # Affichage d'un message de confirmation
    print(mycursor.rowcount, "enregistrement inséré.")


def lister_activiter_sans_fin():
    sql = """
    SELECT *
    FROM activiter
    WHERE date_fin IS NULL OR date_fin = 0
    """

    mycursor.execute(sql)

    return mycursor.fetchall()

def lister_activiter_sans_fin_id(id_action):
    try:
        if not isinstance(id_action, int):
            raise ValueError("id_action doit être un entier")
        sql = """
        SELECT *
        FROM activiter
        WHERE (date_fin IS NULL OR date_fin = 0) and id_nom_action = %s
        """

        mycursor.execute(sql, (id_action,))

        return mycursor.fetchall()
    except mysql.connector.Error as err:
        print("Une erreur s'est produite lors de l'exécution de la requête MySQL:", err)
        return None




def arreter_activiter(id_personne, id_nom_action, date_debut):
    
    nouvelle_date_fin = datetime.now()
    formatted_date = nouvelle_date_fin.strftime('%Y-%m-%d %H:%M:%S')
    # Requête SQL pour mettre à jour la date de fin
    sql = """
    UPDATE activiter
    SET date_fin = %s
    WHERE id_personne = %s AND id_nom_action = %s AND date_debut = %s
    """

    # Exécution de la requête avec les paramètres fournis
    mycursor.execute(sql, (nouvelle_date_fin, id_personne, id_nom_action, date_debut))

    # Commit des changements
    mydb.commit()

def mettre_a_jour_activite(id_personne, id_nom_action, date_debut, compte):

    sql = """UPDATE activiter 
             SET compte = %s
             WHERE id_personne = %s AND id_nom_action = %s AND date_debut = %s
           """
    mycursor.execute(sql, (compte, id_personne, id_nom_action, date_debut))              
    mydb.commit()

def lister_activity():
    # Définition des données à insérer
    
    # Requête SQL pour insérer les données
    sql = """
    SELECT 
        personne.nom AS nom_personne,
        personne.prenom AS prenom_personne,
        nom_action.nom AS nom_action,
        activiter.date_debut,
        activiter.date_fin,
        activiter.compte
    FROM 
        activiter
    JOIN 
        personne ON activiter.id_personne = personne.id
    JOIN 
        nom_action ON activiter.id_nom_action = nom_action.id 
    """
   # Exécution de la requête
    mycursor.execute(sql)
    # recupération des resultat
    result = mycursor.fetchall()
    # Validation de la transaction
    return result

def lister_activity_personne(id_personne):
    # Définition des données à insérer
    sql = """
    SELECT 
        personne.nom AS nom_personne,
        personne.prenom AS prenom_personne,
        nom_action.nom AS nom_action,
        activiter.date_debut,
        activiter.date_fin,
        activiter.compte
    FROM 
        activiter
    JOIN 
        personne ON activiter.id_personne = personne.id
    JOIN 
        nom_action ON activiter.id_nom_action = nom_action.id
    WHERE
        activiter.id_personne = %s 
    """
    # Exécution de la requête
    mycursor.execute(sql, (id_personne,))
    # recupération des resultat
    result = mycursor.fetchall()
    # Validation de la transaction
    return result


#creer_personne("pierre", "mirtille")
#creer_activity(1,2)
#results = lister_activity_personne(1)
#print(results)

#date_debut, id_personne, id_activiter,_ , _ = lister_activiter_sans_fin()[0]
#arreter_activiter(id_personne, id_activiter, date_debut)