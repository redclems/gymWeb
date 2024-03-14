from pushbullet import Pushbullet
from connexion import get_nom_prenom_par_id

def send_notification(text):
    pb = Pushbullet("o.WbJThRVA5C54lb3hi4BzrHCq27Z7ggeS")
    push = pb.push_note(text)

def send_activity_notification(date_debut, id_personne, id_nom_action, date_fin, compte):
    nom, prenom = get_nom_prenom_par_id(id_personne)
    text = f"Activité terminée pour {prenom} {nom}:\nDate de début: {date_debut}\nID personne: {id_personne}\nID nom action: {id_nom_action}\nDate de fin: {date_fin}\nCompte: {compte}"
    send_notification(text)
