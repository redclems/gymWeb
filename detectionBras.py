import cv2 as cv
import numpy as np
import tkinter as tk
import mediapipe as mp
from tkinter import messagebox
import sys

#initializing mediapipe pose
mp_pose = mp.solutions.pose
#initializing mediapipe drawing
mp_drawing = mp.solutions.drawing_utils
#setting up the pose function
pose_video = mp_pose.Pose(static_image_mode = False, min_detection_confidence = 0.5, model_complexity = 1)

# -----------------------Définition des fonctions --------------------------------
#function detection pos
def detectionPose(img, pose, mp_drawing, display = True):
    img_out = img.copy()
    imgRGB = cv.cvtColor(img_out, cv.COLOR_BGR2RGB)
    #Retrieve the height and width of the input image
    height, width,_ = img.shape
    # perfom pose detection
    resultat = pose.process(imgRGB)
    landmarks = []
    # verify if any landmarks are detected
    if resultat.pose_landmarks:
        #draw pose landmarks on the output image
        
        mp_drawing.draw_landmarks(image = img_out, landmark_list = resultat.pose_landmarks, connections = mp_pose.POSE_CONNECTIONS)
        for landmark in resultat.pose_landmarks.landmark:
            #append landmark into the list
            landmarks.append(((landmark.x), (landmark.y), (landmark.z)))
    #check if the original input image and the result image are specified to be displayed
    if display:
        plt.figure(figsize = (22,22))
        plt.subplot(121)
        plt.imshow(img)
        plt.title("Input image")
        plt.subplot(122)
        plt.imshow(img_out)
        plt.title("Output image")
    else:
        return img_out, landmarks

def visualizeAngle(frame, counters, upElements):
    cv.rectangle(frame, (0,0), (225, 75*3), (245,23,23), -1)

    for indice in range(len(counters)):
        cv.putText(frame, 'REPS', (15,12+(75*indice)),
            cv.FONT_HERSHEY_SIMPLEX, .5, (0,0,0), 1, cv.LINE_AA)

        cv.putText(frame , str(counters[indice]), (10,60+(75*indice)),
            cv.FONT_HERSHEY_SIMPLEX, 1.5, (255,255,255), 2, cv.LINE_AA)

        cv.putText(frame, 'STAGE', (75,12+(75*indice)),
            cv.FONT_HERSHEY_SIMPLEX, .5, (0,0,0), 1, cv.LINE_AA)

        if(upElements[indice]):
            cv.putText(frame , "UP", (70,60+(75*indice)),
                cv.FONT_HERSHEY_SIMPLEX, 1.5, (255,255,255), 2, cv.LINE_AA)
        else:
            cv.putText(frame , "DOWN", (70,60+(75*indice)),
                cv.FONT_HERSHEY_SIMPLEX, 1.5, (255,255,255), 2, cv.LINE_AA)  

# fonction calcul de l'angle
def calculAngle(a,b,c):
    a = np.array(a)
    b = np.array(b)
    c = np.array(c)
    radians = np.arctan2(c[1] - b[1], c[0] - b[0]) - np.arctan2(a[1] - b[1], a[0] - b[0])
    angle = np.abs(radians*180.0/np.pi)
    if angle > 180.0:
        angle = 360 - angle
    return angle

# Fonction de fermeture
def on_closing():
    print("programme terminé")
    raise SystemExit()


def detectionBras(affichage=True):
    counter = [0] * 3
    counterTampon = [0] * 3
    upElement = [True] * 3 

    cap = cv.VideoCapture(0)
    registerData = True

    tempVerifFinActiviter = 0;
    # Boucle principale
    while registerData and cap.isOpened():
        ret, frame = cap.read()

        if ret:
            try:
                #detect les landmarks
                newframe, landmarks = detectionPose(frame, pose_video, mp_drawing, False)
            except Exception as e:
                print(f"Une erreur s'est produite : {e}")
                continue
        
            if(landmarks):
                shoulder_left = [landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value][0], landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value][1]]
                elbow_left = [landmarks[mp_pose.PoseLandmark.LEFT_ELBOW.value][0], landmarks[mp_pose.PoseLandmark.LEFT_ELBOW.value][1]]
                wrist_left = [landmarks[mp_pose.PoseLandmark.LEFT_WRIST.value][0], landmarks[mp_pose.PoseLandmark.LEFT_WRIST.value][1]]
                # landmarks for the right arm
                shoulder_right = [landmarks[mp_pose.PoseLandmark.RIGHT_SHOULDER.value][0], landmarks[mp_pose.PoseLandmark.RIGHT_SHOULDER.value][1]]
                elbow_right = [landmarks[mp_pose.PoseLandmark.RIGHT_ELBOW.value][0], landmarks[mp_pose.PoseLandmark.RIGHT_ELBOW.value][1]]
                wrist_right = [landmarks[mp_pose.PoseLandmark.RIGHT_WRIST.value][0], landmarks[mp_pose.PoseLandmark.RIGHT_WRIST.value][1]]

                def angleShoulder(shoulder, elbow, wrist, indice):
                    angleShoulder = calculAngle(shoulder, elbow, wrist)
                    if angleShoulder > 160:
                        upElement[indice] = False
                    if angleShoulder < 30 and upElement[indice] == False and wrist[1] < shoulder[1] and wrist[1] < elbow[1] and shoulder[1] < elbow[1]:
                        upElement[indice] = True
                        counter[indice]  += 1
                        counterTampon[indice] += 1
                
                # Calculer l'angle du bras et les répétitions
                #-------------------------------------------------pour la main gauche------------------------------
                angleShoulder(shoulder_left, elbow_left, wrist_left, 1)
                #---------------------------------------------------pour la main droite--------------------------
                angleShoulder(shoulder_right, elbow_right, wrist_right, 0)
                #--------------------------------------------------Pour les deux mains----------------------------
                if upElement[0] and upElement[1]:
                    if(upElement[2] == False):
                        upElement[2] = True
                        counter[0]  -= 1
                        counter[1]  -= 1
                        counter[2]  += 1
                        counterTampon[0] -= 1
                        counterTampon[1] -= 1
                        counterTampon[2] += 1
                else:
                    upElement[2] = False

                if(affichage):
                    visualizeAngle(newframe, counter, upElement)
                    cv.imshow("mouvement", newframe)

                if tempVerifFinActiviter > 150:
                    for i in range(0,3, 1):
                        incr_les_activiters(i, counterTampon[i])
                        counterTampon[i] = 0
                    tempVerifFinActiviter = 0
                tempVerifFinActiviter += 1
            else:
                if(affichage):
                    cv.imshow("mouvement", frame)
                    

            if cv.waitKey(1) == 27 or cv.waitKey(1) == ord('q'):
                break
        else:
            break


    # Fermer la fenêtre
    cap.release()
    cv.destroyAllWindows()

    return counter

from connexion import lister_activiter_sans_fin_id, mettre_a_jour_activite
from sendNotification import send_end_activity_notification, send_start_activity_notification

liste_save_act = {}  # Initialize liste_save_act as an empty dictionary
def incr_les_activiters(activiter_id, val):
    activiter_id += 1
    global liste_save_act
    
    # Retrieve current activities
    liste_act = lister_activiter_sans_fin_id(activiter_id)
    print(liste_act)

    if activiter_id in liste_save_act:
        missing_elements = [element for element in liste_save_act[activiter_id] if element not in liste_act]
        print("end act : ", missing_elements)
        for date_debut, id_personne, id_nom_action, date_fin, compte in missing_elements:
            send_end_activity_notification(date_debut, id_personne, id_nom_action, date_fin, compte)


    if liste_act:
        missing_elements = [element for element in liste_act if element not in liste_save_act.get(activiter_id, [])]
        #print("start act : ", missing_elements)
        for date_debut, id_personne, id_nom_action, date_fin, compte in missing_elements:
            send_start_activity_notification(date_debut, id_personne, id_nom_action)


    # Update activities and save them in liste_save_act
    for date_debut, id_personne, id_nom_action, _, compte in liste_act:
        mettre_a_jour_activite(id_personne, id_nom_action, date_debut, compte + val)
        #print("activiter ", id_nom_action, " inc de ", val)


    liste_save_act[activiter_id] = liste_act  # Save the updated activities for the activiter_id
counter = detectionBras( affichage=True)
#print(counter)
