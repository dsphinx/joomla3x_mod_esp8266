# Ένθεμα mod_temperature και ESP8266-rpi DHT22 sensors client support

 Κάθε ένθεμα (module) στο Joomla!, υπακούει σε κάποιες συμβάσεις ονοματοδοσίας για να είναι αποδεκτό από τον εσωτερικό μηχανισμό του. Το όνομα του ενθέματος, το δικό μας είναι temperature, θα πρέπει να μετανομαστεί σε mod_temperature, διότι το πρόθεμα mod_ υποδηλώνει ένθετο.
 
Έχοντας ως όνομα του ενθέματος το mod_temperature, έχουμε εν συνεχεία ένα φάκελο με όνομα mod_temperature και ένα αρχείο με όνομα mod_temperature.xml όπου θα περιέχει τις ρυθμίσεις του ενθέματος και ένα βασικό mod_temperature.php όπου θα υπάρχει ο βασικός κώδικας.
Ένα εξίσου σημαντικό αρχείο είναι το helper.php που είναι υπεύθυνο για πρόσβαση στη ΒΔ και εξωτερικές κλήσεις του ενθέματος (mod_ajax). Το υπεύθυνο αρχείο για την προβολή πληροφορίας στο frontend είναι το tmpl/default.php. Η βασική δομή ενός ενθέματος περιέχει αρχεία με κώδικα PHP, αρχεία ini για localization, xml για ρυθμίσεις και μερικά html για την αποτροπή της άμεσης πρόσβασης στο ένθεμα δίχως το Joomla. 

 
 Η  σχεδίαση περιλαμβάνει ένα ένθεμα στη δημοφιλή πλατφόρμα Joomla! CMS, την υλοποίηση εσωτερικού μηχανισμού σε αυτή ως Εndpoint και τον εξωτερικό συλλέκτη πληροφοριών περιβαλλοντικού ενδιαφέροντος DHT22-DHT11 στις πλατφόρμες των οικογενειών


  - Arduino 
  - ESP8266 / LOLIN / WeMos D1
  - raspberry PI 1,2,3

# Υλοποίηση

Η υλοποίηση έγινε σε σε Joomla! 3.9. 

(c) [dsphinx](http://dsphinx.plug.gr) 2019


