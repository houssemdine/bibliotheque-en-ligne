<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categorie;
use App\Entity\Image;
use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class CatController extends AbstractController
{
    /**
     * @Route("/cat", name="cat")
     */
    public function index(): Response
    {
        $entityManager=$this->getDoctrine()->getManager();
        $categorie= new Categorie();
        $categorie->setNomCategorie("Romans");
        $image= new Image();
        $image->setUrl('https://lesoufflenumerique.files.wordpress.com/2015/03/mise-en-page-roman.jpg');
        $image->setAlt('job de reves');
        $categorie->setImage($image);

        $article=new Article();
        $article->setLibelle("Harry Potter et le Prisonnier d'Azkaban");
        $article->setPrix(30.5);
        $article->setAuteur("J.K. Rowling");

        $article1=new Article();
        $article1->setLibelle("Harry Potter et la Chambre des secrets");
        $article1->setPrix(28.5);
        $article1->setAuteur("J.K. Rowling");
       
        $article->setCategorie($categorie);
        $article1->setCategorie($categorie);
       

        $entityManager->persist($article);
        $entityManager->persist($article1);
        $entityManager->flush();



        return $this->render('cat/index.html.twig', [
            'id' => $categorie->getId(),
        ]);
    }

    /**
    * @Route("/cat/{id}", name="cat_show")
    */
    public function show($id,Request $request)
    {
        $categorie = $this->getDoctrine()
        ->getRepository(Categorie::class)
        ->find($id);
        $publicPath = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath().'/uploads/jobs/';

        $em = $this->getDoctrine()->getManager();
        $listeArticle=$em->getRepository(Article::class)->findBy(['Categorie'=>$categorie]);



            if (!$categorie) {
                throw $this->createNotFoundException(
                'No job found for id '.$id
            );
    }
        return $this->render('cat/show.html.twig', [
            'listeArticle' =>$listeArticle,
            'categorie' =>$categorie,
            'publicPath' =>$publicPath
        ]);
    }



   /**
   * @Route("/", name="home")
   */
    public function home(Request $request){
    //creation du champ critere 
    $form = $this->createFormBuilder()
    ->add("critere", TextType::class)
    ->add('valider', SubmitType::class)
    ->getForm();
    $form->handleRequest($request);
    $em=$this->getDoctrine()->getManager();
    $repo = $em ->getRepository(Article::class);
    $lesArticle=$repo->findAll();
    //lancer la recherche quand on clique sur le bouton
        if($form->isSubmitted())
        {
            $data = $form->getData();
            $lesArticle = $repo->recherche($data['critere']);
        }
        return $this->render('cat/home.html.twig', 
            ['lesArticle' => $lesArticle,'form'=>$form->createview()]);
        }


    /**
    * @Route("/Ajouter", name="Ajouter")
    */
    public function ajouter(Request $request){

        $article = new Article();
        $fb= $this ->createFormBuilder($article)
        ->add('libelle',TextType::class)
        ->add('prix',TextType::class)
        ->add('auteur',TextType::class)
        ->add('categorie',EntityType::class,[
            'class'=> Categorie::class,
            'choice_label'=>'nomCategorie'
        ])
        ->add('Valider',SubmitType::class);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em= $this->getDoctrine()->getManager();
            $em ->persist($article);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('cat/ajouter.html.twig',
        ['f'=>$form->createView()]);
    }


    /**
    * @Route("/supp/{id}", name="cand_delete")
    */
    public function delete($id):Response{
        $c = $this->getDoctrine()->getRepository(Article::class)->find($id);
        if (!$c) {
            throw $this->createNotFoundException(
                'No job found for this id '.$id
            );
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($c);
        $entityManager->flush();
        return $this->redirectToRoute('home');
    }

    /**
    * @Route("/editU/{id}", name="edit_user")
    * Method({"GET","POST"})
    */
        public function edit(Request $request, $id){ 
            $article = new Article();
            $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);
            if (!$article) {
            throw $this->createNotFoundException(
            'No article found for id '.$id
            );
            }
            $fb = $this->createFormBuilder($article)
            ->add('libelle', TextType::class)
            ->add('prix', TextType::class, array("label" => "Contenu"))
            ->add('auteur', TextType::class)
            ->add('categorie', EntityType::class, [
            'class' => Categorie::class,
            'choice_label' => 'nomCategorie',
            ])
            ->add('Valider', SubmitType::class);
            // générer le formulaire à partir du FormBuilder
            $form = $fb->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('home');
            }
            return $this->render('cat/ajouter.html.twig',
            ['f' => $form->createView()] );
        }



 /**
     * @Route("/Ajouter_cat", name="Ajouter_cat")
     */
    public function ajouter2(Request $request)
    {
        
        $categorie = new Categorie();
        $form = $this->createForm("App\Form\CategorieType", $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render(
            'cat/ajoutercat.html.twig',
            ['p' => $form->createView()]
        );
    }




/**
     * @Route("/liste", name="listecategorie")
     */

     public function afficherList(Request $request){

        $form=$this->createFormBuilder()
            ->add("nomcategorie",TextType::class)
            ->add("valider",SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Categorie::class);
        $lesCategories=$repo->findAll();

        if($form->isSubmitted()){
            $data= $form->getData();
            $lesCategories=$repo->recherche($data['nomcategorie']);
        }
        return $this->render('cat/liste.html.twig',[
            'lesCategories'=>$lesCategories,
            'form1'=>$form->createView()
        ]);
    }





}
