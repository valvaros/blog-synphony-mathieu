<?php

namespace App\Controller;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\EditArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ArticleController extends AbstractController
{

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {

        $this->entityManager = $entityManager;

    }

    /**
     * @Route("/admin/creer-article", name="create_article")
     * @param Request $request
     * @return Response
     */
    public function createArticle(Request $request, SluggerInterface $slugger)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        
        if($form->isSubmitted() && $form->isValid()){

            $article = $form->getData();
            dd($article);
            # Association de l'article au user : setOwner()
            //

            # Association de l'article à la category : setOwner()
            //

            $article->setCreatedAt(new \DateTime());

            # Coder ici la logique pour uploader la photo
            //On récupère le fichier du formulaire grâce à getData(). Cela nous retourne un objet de type UploadedFile.
             
            $file = $form->get('picture')->getData();

                //Condition qui verifie si un fichier est présent dans le formulaire.
                if($file){

                    //Générer une contrainte d'upload. On déclare un arrray avec deux valeurs de type string qui sont les
                    //MimeType autorisés
                    //Vous retrouvez tous les mimes types existant sur internet
                    $allowedMimeType = ['image/jpeg', 'image/png'];
                    
                    //La fonction native in_array() permet de comparer deux valeurs ( 2 arguments attendus)
                    if(in_array($file->getMimeTypes(), $allowedMimeType)){
                    
                        //Nous allons construire le nouveau nom du fichier

                        //On stock dans une varriable $originalFilename le nom du fichier.
                        //On utilise encore une fonction native pathinfo()
                    $originalFilename = pathinfo($file->getClientOriginaleName());

                        #Récupération de l'éxtention pour pouvoir reconstruire le nom quelques lignes après.
                        //On utilise la concaténation pour ajouter un point '.'
                        $extension = $file->guessExtension();

                        #Assainissement du nom grâce au slugger fourni par Symfony pour la constrution
                        $safeFilename = $slugger->slug($originalFilename);
                        #$safeFilename = $slugger->slug($article->getTitle());


                        #Construction du nouveau nom
                        // uniqid() estune fonction native qui permet de gnérer un Id unique
                        $newFilename= $safeFilename . '_'  . uniqid() . $extension;
                        /*
                        On utilise un try{} catch{} l'orsqu'on appelle une méthode qui lance une erreur.
                        */
                        try{

                            /*On appelle la méthode move() de UploadedFile pour pouvoir déplacer le fichier 
                            dans son dossier de destination.
                            Le dossier de destination a été paramètré dans service.yaml

                            /!\ ATTENTION :
                            La méthode move() lance une erreur de type FileExeception.
                            On attrape cette erreur dans le catch(FileException $exception)

                            */


                            $file->move($this->getParameter('uploads_dir'), $newFilename);

                            //On set la nouvelle valeur (nom du fichier ) de la propriété picture de notre
                            //objet Article.
                            $article ->setPicture($newFilename);

                        } catch(FileException $exception){
                            // code à éxécuter si une erreur est attrapée 
                        }
                }

                $this->entityManager->persist($article);
                $this->entityManager->flush();

                $this->addFlash('success','Article ajouter!');

                return $this->redirectToRoute('dashboard');
            }  
        }

        return $this->render('dashboard/form_article.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/modifier/article/{id}", name="edit_article")
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    public function editArticle(Article $article, Request $request): Response
    {
        # Supprimer le edit form et utiliser ArticleType (configurer les options) : pas besoin de dupliquer un form
        $form = $this->createForm(EditArticleType::class, $article)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            # Créer une nouvelle propriété dans l'entité : setUpdatedAt()

            $this->entityManager->persist($article);
            $this->entityManager->flush();

        }

        return $this->render('article/edit_article.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/voir/article/{id}", name="show_article")
     * @param Article $singleArticle
     * @return Response
     */
    public function showArticle(Article $singleArticle): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($singleArticle->getId());

        return $this->render('article/show_article.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/admin/supprimer/article/{id}", name="delete_article")
     * @param Article $article
     * @return Response
     */
    public function deleteArticle(Article $article): Response
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        $this->addFlash('success','Article supprimé !');

        return $this->redirectToRoute('dashboard');
    }
}
