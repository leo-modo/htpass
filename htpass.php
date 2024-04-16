<?php

$data  = [];
$error = [];

$success = false;

$ip_modo = '85.169.97.162';

$base_path = dirname( __FILE__ ) . '/';
$base_url  = ( empty( $_SERVER['HTTPS'] ) ? 'http' : 'https' ) . "://" . $_SERVER['HTTP_HOST'] . strtok( $_SERVER['REQUEST_URI'], '?' );

$htpasswd_file = $base_path . '.htpasswd';
$htaccess_file = $base_path . '.htaccess';

/**
 * Check if existe .htpasswd and code in htaccess
 */
$is_already_protected = false;

if ( file_exists( $htaccess_file ) ) {
	$htaccess_file_content = file_get_contents( $htaccess_file );
	if ( strpos( $htaccess_file_content, '# BEGIN Modo .htpasswd' ) !== false ) {
		$is_already_protected = true;
	}
}

if ( file_exists( $htpasswd_file ) ) {
	$is_already_protected = true;
}


/**
 * Send form
 */
if ( ! empty( $_POST ) && ! $is_already_protected ) {

	/**
	 * Check login
	 */
	if ( ! empty( $_POST['login'] ) ) {
		$data['login'] = htmlentities( $_POST['login'] );
	} else {
		$error['login'] = 'Saisir un identifiant';
	}

	/**
	 * Check password
	 */
	if ( ! empty( $_POST['password'] ) ) {
		if ( strlen( $_POST['password'] ) >= 5 ) {
			$data['password'] = htmlentities( $_POST['password'] );
		} else {
			$error['password'] = 'Mot de passe trop court';
		}
	} else {
		$error['password'] = 'Saisir un mot de passe';
	}

	/**
	 * Set IPs
	 */
	if ( ! empty( $_POST['login'] ) ) {
		$data['ip'] = htmlentities( $_POST['ip'] );
	}


	if ( ! empty( $data ) && empty( $error ) ) {

		/**
		 * Create htpasswd file
		 */

		$sel            = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ), 0, 2 );
		$htpass_pass    = crypt( $data['password'], $sel );
		$htpass_content = $data['login'] . ':' . $htpass_pass;

		$htpasswd = fopen( $htpasswd_file, "w" ) or die( "Unable to open file!" );
		fwrite( $htpasswd, $htpass_content );
		fclose( $htpasswd );

		/**
		 * Add to htaccess
		 */
		$htaccess_code = "\n" . '# BEGIN Modo .htpasswd' . "\n";
		$htaccess_code .= 'AuthName "Preprod - Modo"' . "\n";
		$htaccess_code .= 'AuthUserFile ' . $htpasswd_file . "\n";
		$htaccess_code .= 'AuthType Basic' . "\n";
		$htaccess_code .= 'require valid-user' . "\n";
		$htaccess_code .= 'satisfy any' . "\n";
		$htaccess_code .= 'deny from all' . "\n";
		$htaccess_code .= 'Allow from ' . $ip_modo . "\n";

		if ( ! empty( $data['ip'] ) ) {
			$data['ip'] = explode( ',', $data['ip'] );

			foreach ( $data['ip'] as $ip ) {
				$htaccess_code .= "Allow from " . trim( $ip ) . "\n";
			}
		}

		$htaccess_code .= "# END Modo .htpasswd" . "\n";

		$htaccess = fopen( $base_path . '.htaccess', "a" ) or die( "Unable to open file!" );
		fwrite( $htaccess, $htaccess_code );
		fclose( $htaccess );

		$success = true;

	}
}

/**
 * Reinit
 */
if ( isset( $_GET['reinit'] ) ) {

	// Suppression fichier htpasswd
	if ( file_exists( $htpasswd_file ) ) {
		unlink( $htpasswd_file );
	}

	// Suppression code htaccess
	if ( file_exists( $htaccess_file ) ) {
		$contenu = file_get_contents( $htaccess_file );
		$debut   = strpos( $contenu, "# BEGIN Modo .htpasswd" );
		if ( $debut !== false ) {
			$fin = strpos( $contenu, "# END Modo .htpasswd", $debut );
			if ( $fin !== false ) {
				$contenu = substr_replace( $contenu, '', $debut, $fin - $debut + strlen( "# END Modo .htpasswd" ) );
				file_put_contents( $htaccess_file, $contenu );
			}
		}
	}

    header( 'Location: ' . $base_url );
}

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Générateur de htpasswd</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">

    <style>
      * {
        box-sizing: border-box;
      }

      html {
        height: 100%;
      }

      body {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 100%;
        height: 100%;
        min-height: 100%;

        background: #000;

        font-family: 'Helvetica', Arial, serif;
        line-height: 1.5em;
        font-size: 16px;
      }

      #header {
        color: #FECE32;
        text-align: center;
      }

      #header h1 {
        margin-bottom: 10px;
        font-size: 20px;
        text-transform: uppercase;
      }

      #header p {
        margin-bottom: 30px;
        color: #fff;
      }

      #wrapper #container {
        max-width: 500px;

        padding: 60px;

        background: #fff;
        border-radius: 3px;
      }

      #wrapper .form-notice {
        padding: 20px;
        margin-bottom: 32px;

        background: #aad29d;
        border-radius: 3px;

        color: #3c7a21;
        text-align: center;
      }

      #wrapper .form-notice.is-error {
        background: #d29d9d;
        color: #9f1f1f;
      }

      #wrapper .form-field + .form-field {
        margin-top: 20px;
      }

      #wrapper label {
        display: block;

        margin-bottom: 5px;
      }

      #wrapper .description {
        margin-bottom: 10px;
        font-size: 12px;
        color: #757575;
      }

      #wrapper input,
      #wrapper textarea {
        width: 100%;
        height: 40px;

        padding: 0 10px;

        border-radius: 3px;
        border: 1px solid #ddd;
      }

      #wrapper textarea {
        height: 70px;

        padding: 10px;
      }

      #wrapper button {
        display: block;

        width: 100%;
        height: 52px;

        background: #FECE32;
        border: none;
        border-radius: 3px;

        font-size: 16px;
      }

      #wrapper button:hover {
        background: #000;
        color: #FECE32;
      }

      #wrapper .field-error {
        margin-top: 5px;

        color: #9f1f1f;
        font-size: 12px;
      }

      #success .box + .box {
        margin-top: 20px;
      }

      #success .box .title {
        margin-bottom: 12px;
        font-weight: bold;
      }

      #success .box .content {
        display: block;
        width: 100%;

        margin: 0;

        color: #757575;
        font-size: 12px;
      }

      #already_protected {
        text-align: center;
      }

      #already_protected p {
        margin-bottom: 12px;
      }

      #already_protected a {
        color: #9f1f1f;
      }

    </style>
</head>
<body>

<div id="wrapper">

    <div id="header">
        <img width="100"
             src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABDkAAAQ5CAYAAADIjtQUAAAACXBIWXMAAAsSAAALEgHS3X78AAAgAElEQVR4nOzd/VEkR5c+7JJq/2d+FoAsGKocgLVgWAsGWSBkgWYsELJAjAViLBA4UAUWCCx4BwMqeKO0ybOt0TBDQ39knryuiA5pnwjFwskuuuuukye/u7+/bwAAAABK970VBAAAACIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABCCkAMAAAAIQcgBAAAAhCDkAAAAAEIQcgAAAAAhCDkAAACAEIQcAAAAQAhCDgAAACAEIQcAAAAQgpADAAAACEHIAQAAAIQg5AAAAABCEHIAAAAAIQg5AAAAgBCEHAAAAEAIQg4AAAAgBCEHAAAAEIKQAwAAAAhByAEAAACEIOQAAAAAQhByAAAAACEIOQAAAIAQhBwAAABACEIOAAAAIAQhBwAAABDCf1lGAKax32ua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">

        <h1>Sécuriser mon site</h1>
        <p>Ajoutez une protection htaccess / htpasswd sur votre site</p>
    </div>

    <div id="container">

		<?php if ( $success ): ?>
            <div id="success">
                <div class="form-notice">Votre site est protégé</div>
                <div class="box">
                    <p class="title">Identifiants :</p>
                    <p class="content">
                        Identifiant : <?php echo $data['login']; ?><br>
                        Mot de passe : <?php echo $data['password']; ?>
                    </p>
                </div>
                <div class="box">
                    <p class="title">Code htaccess :</p>
                    <p class="content"><?php echo nl2br( $htaccess_code ); ?></p>
                </div>
            </div>

		<?php elseif ( $is_already_protected ): ?>
            <div id="already_protected">
                <p> Une protection htpasswd est déjà en place sur votre site. Vous pouvez réinitialiser en supprimant la protection actuelle</p>
                <p>Avant de tout supprimez, assurez vous de ne pas faire d'erreur</p>
                <a href="<?php echo $base_url . '?reinit'; ?>" onclick="confirm('Etes-vous bien certain de ce que vous fiates ?')">Réinitialiser</a>
            </div>
		<?php else: ?>
            <form method="post" action="<?php echo $base_url; ?>">

				<?php if ( ! empty( $error ) ): ?>
                    <div class="form-notice is-error">Vous avez des erreurs</div>
				<?php endif; ?>

                <div class="form-field">
                    <label for="login">Identifiant</label>
					<?php $login = explode( '.', $_SERVER['HTTP_HOST'] ); ?>
                    <input type="text" id="login" name="login" placeholder="<?php echo $login[0]; ?>"
                           value="<?php echo ( ! empty( $data['login'] ) ) ? $data['login'] : ''; ?>">
					<?php if ( ! empty( $error['login'] ) ): ?>
                        <p class="field-error"><?php echo $error['login']; ?></p>
					<?php endif; ?>
                </div>
                <div class="form-field">
                    <label for="password">Mot de passe</label>
                    <input type="text" id="password" name="password" placeholder="@Modo#<?php echo date( 'Y' ); ?>!"
                           value="<?php echo ( ! empty( $data['password'] ) ) ? $data['password'] : ''; ?>">
					<?php if ( ! empty( $error['password'] ) ): ?>
                        <p class="field-error"><?php echo $error['password']; ?></p>
					<?php endif; ?>
                </div>
                <div class="form-field">
                    <label for="ip">Autoriser des IPs</label>
                    <p class="description">Séparez les IP par des virgules. <br>L'ip de Mōdo est automatiquement ajoutée</p>
                    <textarea id="ip" name="ip" placeholder="85.169.97.162"><?php echo ( ! empty( $data['ip'] ) ) ? $data['ip'] : ''; ?></textarea>
                </div>
                <div class="form-field">
                    <button type="submit">Sécuriser mon site</button>
                </div>
            </form>

		<?php endif; ?>
    </div>
</div>

</body>
</html>