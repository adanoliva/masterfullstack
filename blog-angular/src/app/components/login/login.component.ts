import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Params, Router} from '@angular/router';
import { User } from '../../models/user';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  providers: [UserService]
})
export class LoginComponent implements OnInit {
  public page_title: string;
  public user: User;
  public status: string;
  public token;
  public identity;

  constructor(
    private _userService: UserService,
    private _router: Router,
    private _route: ActivatedRoute

  ) {
    this.page_title = "Identifícate";
    this.user = new User(1, '', '', 'ROLE_USER', '', '', '', '');
   }

  ngOnInit() {
    //Se ejecuta siempre que se llame al componente y cierra sesión solamente si llega el parámetro sure
    this.logout();
  }
  onSubmit(form) {

    this._userService.signup(this.user).subscribe(
      response =>{
        if (response.status != "error"){
          //Devuelve el TOKEN
          this.status = "success";
          this.token = response;

          //Objeto usuario identificado
          this._userService.signup(this.user,'true').subscribe(
            response =>{
              this.identity = response;
              console.log(this.token);
              console.log(this.identity);
              //Persistir datos de usuario identificado
              localStorage.setItem('token', this.token);
              localStorage.setItem('identity', JSON.stringify(this.identity));

              //Volemos a la página de inicio
              this._router.navigate(['inicio'])

            },
            error =>{
              //this.status = "error";
              console.log(<any>error);
            }
          );
        }
        else{
          this.status = "error";
        }

      },
      error =>{
        this.status = "error";
        console.log(<any>error);
      }
    );
  }

  logout()
  {
    this._route.params.subscribe(
      params =>{
        let logout = +params['sure'];

        if(logout==1){
          //Elimina los datos de la localStorage
          localStorage.removeItem('identity');
          localStorage.removeItem('token');

          //Deja a null los datos del componente
          this.identity = null;
          this.token = null;

          //Volemos a la página de inicio
          this._router.navigate(['inicio']);

        }

      }
    );
  }

}
