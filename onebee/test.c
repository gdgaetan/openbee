// Exemple de programme C qui va afficher
// sur l'écran, le message "Hello world !!"

#include <stdio.h>

int main ()
{
FILE* fichier = NULL;
fichier = fopen("test.txt", "w");
if (fichier != NULL)
{
fputs("Salut les Zér0s\nComment allez-vous ?", fichier);
fclose(fichier);
}
return 0;
}
