<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request)
    {
         //Récupère doctrine
         $em = $this->getDoctrine()->getManager();
         $un_produit=new Produit();
         $form=$this->createForm(ProduitType::class, $un_produit);
         $form->handleRequest($request);

         if($form->isSubmitted()){
            //Toutes les brochures on été remplacé par fichier , le get brochure en DrapeauUpload
            $fichier = $form->get('photoUpload')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($fichier) {
            
                $newFilename = uniqid().'.'.$fichier->guessExtension();
                //Nom de notre fichier
                // Move the file to the directory where brochures are stored
                try {
                    $fichier->move(
                        $this->getParameter('upload_directory'),// nous on l'appel upload, on vas devoir le créer dans config service
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', "Impossible d'uploader le fichier");
                }

                // updates the 'fichiername' property to store the PDF file name
                // instead of its contents
                $un_produit->setPhoto($newFilename);
            }

            $em -> persist($un_produit);
            $em-> flush();

            $this->addFlash('SUCCES', "le fichier à été  uploader ");
        }
         // récuper la table produit
         $pays= $em->getRepository(Produit::class)->findAll();


        return $this->render('produit/index.html.twig', [
            'produit' => 'produit',
            'ajout' => $form->createView()
        ]);
    }
}
