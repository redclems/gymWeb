from pushbullet import Pushbullet
from connexion import get_nom_prenom_par_id, get_nom_activiter_par_id

def send_notification(title, text):
    pb = Pushbullet("o.WbJThRVA5C54lb3hi4BzrHCq27Z7ggeS")
    push = pb.push_note(title, text)

def send_end_activity_notification(date_debut, id_personne, id_nom_action, date_fin, compte):
    nom, prenom = get_nom_prenom_par_id(id_personne)
    nom_action = get_nom_activiter_par_id(id_nom_action)
    text = f"Activité terminée pour {prenom} {nom}:\nDate de début: {date_debut}\nnom action: {nom_action}\nDate de fin: {date_fin}\nCompte: {compte}"
    send_notification("fin d'activiter", text)

def send_start_activity_notification(date_debut, id_personne, id_nom_action):
    nom, prenom = get_nom_prenom_par_id(id_personne)
    nom_action = get_nom_activiter_par_id(id_nom_action)
    text = f"Activité commence pour {prenom} {nom}:\nDate de début: {date_debut}\nnom action: {nom_action}\n"
    send_notification("fin d'activiter", text)